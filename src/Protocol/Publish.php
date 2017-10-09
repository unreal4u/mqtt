<?php

declare(strict_types=1);

namespace unreal4u\MQTT\Protocol;

use unreal4u\MQTT\Application\EmptyReadableResponse;
use unreal4u\MQTT\Application\Message;
use unreal4u\MQTT\Application\SimplePayload;
use unreal4u\MQTT\Client;
use unreal4u\MQTT\Internals\CommonFunctionality;
use unreal4u\MQTT\Internals\ReadableContent;
use unreal4u\MQTT\Internals\ReadableContentInterface;
use unreal4u\MQTT\Internals\WritableContent;
use unreal4u\MQTT\Internals\WritableContentInterface;
use unreal4u\MQTT\Utilities;

final class Publish implements ReadableContentInterface, WritableContentInterface
{
    use CommonFunctionality;
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

        if ($this->isRedelivery) {
            // DUP flag: if the message is a re-delivery, mark it as such
            $this->specialFlags &= 4;
        }

        if ($this->message->mustRetain()) {
            // RETAIN flag: should the server retain the message?
            $this->specialFlags &= 64;
        }

        // Check QoS level and perform the corresponding actions
        switch ($this->message->getQoSLevel()) {
            case 1:
                $this->specialFlags &= 8;
                $bitString .= Utilities::convertNumberToBinaryString($this->packetIdentifier);
                break;
            case 2:
                $this->specialFlags &= 16;
                $bitString .= Utilities::convertNumberToBinaryString($this->packetIdentifier);
                break;
            default:
                break;
        }

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

    public function setMessage(Message $message): WritableContentInterface
    {
        $this->message = $message;
        return $this;
    }

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
        $topicSize = ord($this->rawMQTTHeaders{3});

        $simplePayload = new SimplePayload();
        $simplePayload->setPayload(substr($this->rawMQTTHeaders, 4 + $topicSize));

        $this->message = new Message();
        $this->message->setPayload($simplePayload);
        $this->message->setTopicName(substr($this->rawMQTTHeaders, 4, $topicSize));

        return $this;
    }

    /**
     * Some operations require setting some things in the client, this hook will do so
     *
     * @param Client $client
     * @return bool
     * @throws \unreal4u\MQTT\Exceptions\ServerClosedConnection
     * @throws \unreal4u\MQTT\Exceptions\NotConnected
     * @throws \unreal4u\MQTT\Exceptions\InvalidMethod
     * @throws \unreal4u\MQTT\Exceptions\Connect\NoConnectionParametersDefined
     */
    public function performSpecialActions(Client $client): bool
    {
        $client->setBlocking(true);
        $qosLevel = $this->message->getQoSLevel();
        switch ($qosLevel) {
            case 1:
                $this->logger->debug('Responding with PubAck', ['qosLevel' => $qosLevel]);
                $client->sendData(new PubAck($this->logger));
                break;
            case 2:
                $this->logger->debug('Responding with PubRec', ['qosLevel' => $qosLevel]);
                $client->sendData(new PubRec($this->logger));
                break;
            default:
                $this->logger->debug('No response needed', ['qosLevel', $qosLevel]);
                break;
        }
        $client->setBlocking(false);

        return true;
    }
}
