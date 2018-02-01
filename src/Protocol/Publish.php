<?php

declare(strict_types=1);

namespace unreal4u\MQTT\Protocol;

use unreal4u\MQTT\Application\EmptyReadableResponse;
use unreal4u\MQTT\Application\Message;
use unreal4u\MQTT\Application\Topic;
use unreal4u\MQTT\DataTypes\QoSLevel;
use unreal4u\MQTT\Internals\ClientInterface;
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
    use ReadableContent, WritableContent;

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
     * @return string
     * @throws \unreal4u\MQTT\Exceptions\InvalidQoSLevel
     * @throws \unreal4u\MQTT\Exceptions\MissingTopicName
     * @throws \OutOfRangeException
     * @throws \InvalidArgumentException
     */
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
            // 0 for QoS lvl2 for QoS lvl1 and 4 for QoS lvl2
            $this->specialFlags |= ($this->message->getQoSLevel() * 2);
            $bitString .= Utilities::convertNumberToBinaryString($this->packetIdentifier);
            $this->logger->debug(sprintf('Activating QoS level %d bit', $this->message->getQoSLevel()), [
                'specialFlags' => $this->specialFlags,
            ]);
        }

        if ($this->message->isRetained()) {
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
     * @throws \unreal4u\MQTT\Exceptions\InvalidQoSLevel
     */
    public function shouldExpectAnswer(): bool
    {
        $shouldExpectAnswer = !($this->message->getQoSLevel() === 0);
        $this->logger->debug('Checking whether we should expect an answer or not', [
            'shouldExpectAnswer' => $shouldExpectAnswer,
        ]);
        return $shouldExpectAnswer;
    }

    public function expectAnswer(string $data, ClientInterface $client): ReadableContentInterface
    {
        switch ($this->message->getQoSLevel()) {
            case 1:
                $pubAck = new PubAck($this->logger);
                $pubAck->instantiateObject($data, $client);
                return $pubAck;
            case 2:
                $pubRec = new PubRec($this->logger);
                $pubRec->instantiateObject($data, $client);
                return $pubRec;
            case 0:
            default:
                return new EmptyReadableResponse($this->logger);
        }
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
        $this->logger->debug('Analyzing first byte', [sprintf('%08d', decbin($firstByte))]);
        // Retained bit is bit 0 of first byte
        $this->message->setRetainFlag(false);
        if ($firstByte & 1) {
            $this->message->setRetainFlag(true);
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
     * @return QoSLevel
     * @throws \unreal4u\MQTT\Exceptions\InvalidQoSLevel
     */
    private function determineIncomingQoSLevel(int $bitString): QoSLevel
    {
        // Strange operation, why? Because 4 == QoS lvl2; 2 == QoS lvl1, 0 == QoS lvl0
        return new QoSLevel($bitString & 4 / 2);
    }

    /**
     * Will perform sanity checks and fill in the Readable object with data
     * @param string $rawMQTTHeaders
     * @param ClientInterface $client
     * @return ReadableContentInterface
     * @throws \OutOfBoundsException
     * @throws \unreal4u\MQTT\Exceptions\InvalidQoSLevel
     * @throws \InvalidArgumentException
     * @throws \OutOfRangeException
     */
    public function fillObject(string $rawMQTTHeaders, ClientInterface $client): ReadableContentInterface
    {
        if (\strlen($rawMQTTHeaders) === 1) {
            $this->logger->debug('Fast check, read rest of data from socket');
            $restOfBytes = $client->readSocketData(1);
            $payload = $client->readSocketData(\ord($restOfBytes));
        } else {
            $this->logger->debug('Slow form, retransform data and read rest of data');
            $restOfBytes = $rawMQTTHeaders{1};
            $payload = substr($rawMQTTHeaders, 2);
            $exactRest = \ord($restOfBytes) - \strlen($payload);
            $payload .= $client->readSocketData($exactRest);
            $rawMQTTHeaders = $rawMQTTHeaders{0};
        }

        // At this point, $rawMQTTHeaders will be always 1 byte long
        $this->message = new Message();
        $this->analyzeFirstByte(\ord($rawMQTTHeaders));
        // $rawMQTTHeaders may be redefined
        $rawMQTTHeaders = $rawMQTTHeaders . $restOfBytes . $payload;

        // Topic size is always the 3rd byte
        $topicSize = \ord($rawMQTTHeaders{3});

        $messageStartPosition = 4;
        if ($this->message->getQoSLevel() > 0) {
            // [2 (fixed header) + 2 (topic size) + $topicSize] marks the beginning of the 2 packet identifier bytes
            $this->packetIdentifier = Utilities::convertBinaryStringToNumber(
                $rawMQTTHeaders{5 + $topicSize} . $rawMQTTHeaders{4 + $topicSize}
            );
            $messageStartPosition += 2;
        }

        $this->logger->debug('Determined headers', [
            'topicSize' => $topicSize,
            'QoSLevel' => $this->message->getQoSLevel(),
            'isDuplicate' => $this->isRedelivery,
            'isRetained' => $this->message->isRetained(),
            'packetIdentifier' => $this->packetIdentifier,
        ]);

        $this->message->setPayload(substr($rawMQTTHeaders, $messageStartPosition + $topicSize));
        $this->message->setTopic(new Topic(substr($rawMQTTHeaders, $messageStartPosition, $topicSize)));

        return $this;
    }

    /**
     * @inheritdoc
     * @throws \unreal4u\MQTT\Exceptions\InvalidQoSLevel
     * @throws \unreal4u\MQTT\Exceptions\ServerClosedConnection
     * @throws \unreal4u\MQTT\Exceptions\NotConnected
     * @throws \unreal4u\MQTT\Exceptions\Connect\NoConnectionParametersDefined
     */
    public function performSpecialActions(ClientInterface $client, WritableContentInterface $originalRequest): bool
    {
        $qosLevel = $this->message->getQoSLevel();
        if ($qosLevel === 0) {
            $this->logger->debug('No response needed', ['qosLevel', $qosLevel]);
        } else {
            if ($qosLevel === 1) {
                $this->logger->debug('Responding with PubAck', ['qosLevel' => $qosLevel]);
                $client->sendData($this->composePubAckAnswer());
            } elseif ($qosLevel === 2) {
                $this->logger->debug('Responding with PubRec', ['qosLevel' => $qosLevel]);
                $client->sendData($this->composerPubRecAnswer());
            }
        }

        return true;
    }

    private function composerPubRecAnswer(): PubRec
    {
        $pubRec = new PubRec($this->logger);
        $pubRec->packetIdentifier = $this->packetIdentifier;
        return $pubRec;
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
