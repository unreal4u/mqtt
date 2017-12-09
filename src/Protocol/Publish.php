<?php

declare(strict_types=1);

namespace unreal4u\MQTT\Protocol;

use unreal4u\MQTT\Application\EmptyReadableResponse;
use unreal4u\MQTT\Application\Message;
use unreal4u\MQTT\Application\PayloadInterface;
use unreal4u\MQTT\Client;
use unreal4u\MQTT\Exceptions\InvalidQoSLevel;
use unreal4u\MQTT\Internals\ProtocolBase;
use unreal4u\MQTT\Internals\ReadableContent;
use unreal4u\MQTT\Internals\ReadableContentInterface;
use unreal4u\MQTT\Internals\WritableContent;
use unreal4u\MQTT\Internals\WritableContentInterface;
use unreal4u\MQTT\Utilities;

/**
 * A PUBLISH Control Packet is sent from a Client to a Server or vice-versa to transport an Application Message.
 *
 * @see http://docs.oasis-open.org/mqtt/mqtt/v3.1.1/os/mqtt-v3.1.1-os.html#_Toc398718037
 */
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

    /**
     * Some interchanges with the broker will send or receive a packet identifier
     * @var int
     */
    public $packetIdentifier = 0;

    /**
     * Flag to check whether a message is a redelivery (DUP flag)
     * @see http://docs.oasis-open.org/mqtt/mqtt/v3.1.1/os/mqtt-v3.1.1-os.html#_Toc398718038
     * @var bool
     */
    public $isRedelivery = false;

    /**
     * @var PayloadInterface
     */
    private $payloadType;

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
        if ($this->message->getQoSLevel() !== 0) {
            // 2 for QoS lvl1 and 4 for QoS lvl2
            $this->specialFlags |= ($this->message->getQoSLevel() * 2);
            $this->packetIdentifier++;
            $bitString .= Utilities::convertNumberToBinaryString($this->packetIdentifier);
            $this->logger->debug(sprintf('Activating QoS level %d bit', $this->message->getQoSLevel()), [
                'specialFlags' => $this->specialFlags,
            ]);
        }

        if ($this->message->mustRetain()) {
            // RETAIN flag: should the server retain the message?
            $this->specialFlags |= 1;
            $this->logger->debug('Activating retain flag', ['specialFlags' => $this->specialFlags]);
        }

        $this->logger->info('Variable header created', ['specialFlags' => $this->specialFlags]);

        return $bitString;
    }

    /**
     * Sets the specific payload we should be listening for on this topic(s)
     *
     * @param PayloadInterface $payloadType
     * @return Publish
     */
    public function setPayloadType(PayloadInterface $payloadType): Publish
    {
        $this->payloadType = $payloadType;

        return $this;
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
     * Sets several bits and pieces from the first byte of the fixed header for the Publish packet
     *
     * @param int $firstByte
     * @return Publish
     * @throws \unreal4u\MQTT\Exceptions\InvalidQoSLevel
     */
    private function analyzeFirstByte(int $firstByte): Publish
    {
        // Retained bit is bit 0 of first byte
        $this->message->shouldRetain(false);
        if ($firstByte & 1) {
            $this->message->shouldRetain(true);
        }
        // QoS level are the last bits 2 & 1 of the first byte
        $this->message->setQoSLevel($this->determineIncomingQoSLevel($firstByte));

        // Duplicate message must be checked only on QoS > 0, else set it to false
        $this->isRedelivery = false;
        if ($firstByte & 8 && $this->message->getQoSLevel() !== 0) {
            // Is a duplicate is always bit 3 of first byte
            $this->isRedelivery = true;
        }

        return $this;
    }

    /**
     * Finds out the QoS level in a fixed header for the Publish object
     *
     * @param int $bitString
     * @return int
     * @throws \unreal4u\MQTT\Exceptions\InvalidQoSLevel
     */
    private function determineIncomingQoSLevel(int $bitString): int
    {
        // QoS lvl 6 does not exist, throw exception
        if (($bitString & 6) >= 6) {
            throw new InvalidQoSLevel('Invalid QoS level "' . $bitString . '" found (both bits set?)');
        }

        // Strange operation, why? Because 4 == QoS lvl2; 2 == QoS lvl1, 0 == QoS lvl0
        return $bitString & 4 / 2;
    }

    /**
     * Will perform sanity checks and fill in the Readable object with data
     * @param string $rawMQTTHeaders
     * @return ReadableContentInterface
     * @throws \unreal4u\MQTT\Exceptions\InvalidQoSLevel
     */
    public function fillObject(string $rawMQTTHeaders): ReadableContentInterface
    {
        $this->message = new Message();
        $this->analyzeFirstByte(\ord($rawMQTTHeaders{0}));

        // Topic size is always the 3rd byte
        $topicSize = \ord($rawMQTTHeaders{3});

        $messageStartPosition = 4;
        if ($this->message->getQoSLevel() > 0) {
            // 2 (fixed header) + 2 (topic size) + $topicSize marks the beginning of the 2 packet identifier bytes
            $this->packetIdentifier = \ord($rawMQTTHeaders{5 + $topicSize} . $rawMQTTHeaders{4 + $topicSize});
            $messageStartPosition += 2;
        }

        $this->logger->debug('Determined headers', [
            'topicSize' => $topicSize,
            'QoSLevel' => $this->message->getQoSLevel(),
            'isDuplicate' => $this->isRedelivery,
            'isRetained' => $this->message->mustRetain(),
            'packetIdentifier' => $this->packetIdentifier,
        ]);
        $payload = clone $this->payloadType;

        $this->message->setPayload($payload->setPayload(substr($rawMQTTHeaders, $messageStartPosition + $topicSize)));
        $this->message->setTopicName(substr($rawMQTTHeaders, $messageStartPosition, $topicSize));

        return $this;
    }

    /**
     * @inheritdoc
     * @throws \unreal4u\MQTT\Exceptions\ServerClosedConnection
     * @throws \unreal4u\MQTT\Exceptions\NotConnected
     * @throws \unreal4u\MQTT\Exceptions\Connect\NoConnectionParametersDefined
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
