<?php

declare(strict_types=1);

namespace unreal4u\MQTT\Protocol;

use unreal4u\MQTT\Application\EmptyReadableResponse;
use unreal4u\MQTT\Application\Message;
use unreal4u\MQTT\Application\SimplePayload;
use unreal4u\MQTT\Client;
use unreal4u\MQTT\Internals\ProtocolBase;
use unreal4u\MQTT\Internals\ReadableContent;
use unreal4u\MQTT\Internals\ReadableContentInterface;
use unreal4u\MQTT\Internals\WritableContent;
use unreal4u\MQTT\Internals\WritableContentInterface;
use unreal4u\MQTT\Utilities;

final class Publish extends ProtocolBase implements ReadableContentInterface, WritableContentInterface
{
    use ReadableContent;
    use WritableContent;

    const CONTROL_PACKET_VALUE = 3;

    /**
     * Contains the message to be sent
     * @var Message
     */
    private $message;

    public $packetIdentifier = 0;

    public $isRedelivery = false;

    public function createVariableHeader(): string
    {
        if ($this->message === null) {
            throw new \InvalidArgumentException('You must at least provide a message object with a topic name');
        }

        $bitString = $this->createUTF8String($this->message->getTopicName());
        // Reset the special flags should the object be reused with another message
        $this->specialFlags = 0;

        if ($this->isRedelivery) {
            $this->logger->debug('Activating redelivery bit');
            // DUP flag: if the message is a re-delivery, mark it as such
            $this->specialFlags |= 8;
        }

        // Check QoS level and perform the corresponding actions
        switch ($this->message->getQoSLevel()) {
            case 1:
                $this->specialFlags |= 2;
                $this->logger->debug('Activating QoS level 1 bit', ['specialFlags' => $this->specialFlags]);
                $this->packetIdentifier++;
                $bitString .= Utilities::convertNumberToBinaryString($this->packetIdentifier);
                break;
            case 2:
                $this->specialFlags |= 4;
                $this->logger->debug('Activating QoS level 2 bit', ['specialFlags' => $this->specialFlags]);
                $this->packetIdentifier++;
                $bitString .= Utilities::convertNumberToBinaryString($this->packetIdentifier);
                break;
            default:
                break;
        }

        if ($this->message->mustRetain()) {
            // RETAIN flag: should the server retain the message?
            $this->specialFlags |= 1;
            $this->logger->debug('Activating retain flag', ['specialFlags' => $this->specialFlags]);
        }

        $this->logger->info('Variable header created', ['specialFlags' => $this->specialFlags]);

        return $bitString;
    }

    public function createPayload(): string
    {
        if (!$this->message->validateMessage()) {
            throw new \InvalidArgumentException('Invalid message');
        }

        return $this->message->getPayload();
    }

    /**
     * QoS level 0 does not have to wait for a answer, so return false. Any other QoS level returns true
     * @return bool
     */
    public function shouldExpectAnswer(): bool
    {
        return !($this->message->getQoSLevel() === 0);
    }

    public function expectAnswer(string $data): ReadableContentInterface
    {
        if ($this->shouldExpectAnswer() === false) {
            return new EmptyReadableResponse($this->logger);
        }

        $pubAck = new PubAck($this->logger);
        $pubAck->populate($data);
        return $pubAck;
    }

    /**
     * Sets the to be sent message
     *
     * @param Message $message
     * @return WritableContentInterface
     */
    public function setMessage(Message $message): WritableContentInterface
    {
        $this->message = $message;
        return $this;
    }

    /**
     * Gets the set message
     *
     * @return Message
     */
    public function getMessage(): Message
    {
        return $this->message;
    }

    /**
     * Will perform sanity checks and fill in the Readable object with data
     * @return ReadableContentInterface
     */
    public function fillObject(): ReadableContentInterface
    {
        $topicSize = \ord($this->rawMQTTHeaders{3});

        $this->message = new Message();
        $this->message->setPayload(new SimplePayload(substr($this->rawMQTTHeaders, 4 + $topicSize)));
        $this->message->setTopicName(substr($this->rawMQTTHeaders, 4, $topicSize));

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function performSpecialActions(Client $client, WritableContentInterface $originalRequest): bool
    {
        if ($this->message->getQoSLevel() === 0) {
            $this->logger->debug('No response needed', ['qosLevel', $this->message->getQoSLevel()]);
        } else {
            $client->setBlocking(true);
            if ($this->message->getQoSLevel() === 1) {
                $this->logger->debug('Responding with PubAck', ['qosLevel' => $this->message->getQoSLevel()]);
                $client->sendData($this->composePubAckAnswer());
            } elseif ($this->message->getQoSLevel() === 2) {
                $this->logger->debug('Responding with PubRec', ['qosLevel' => $this->message->getQoSLevel()]);
                $client->sendData(new PubRec($this->logger));
            }
            $client->setBlocking(false);
        }

        return true;
    }

    /**
     * Composes a PubAck answer with the same packetIdentifier as what we received
     * @return PubAck
     */
    private function composePubAckAnswer(): PubAck
    {
        $pubAck = new PubAck($this->logger);
        $pubAck->packetIdentifier = $this->packetIdentifier;
        return $pubAck;
    }
}
