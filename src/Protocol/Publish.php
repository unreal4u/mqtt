<?php

declare(strict_types=1);

namespace unreal4u\MQTT\Protocol;

use unreal4u\MQTT\Application\EmptyReadableResponse;
use unreal4u\MQTT\DataTypes\Message;
use unreal4u\MQTT\DataTypes\PacketIdentifier;
use unreal4u\MQTT\DataTypes\Topic;
use unreal4u\MQTT\DataTypes\QoSLevel;
use unreal4u\MQTT\Internals\ClientInterface;
use unreal4u\MQTT\Internals\PacketIdentifierFunctionality;
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
    use ReadableContent, WritableContent, PacketIdentifierFunctionality;

    const CONTROL_PACKET_VALUE = 3;

    /**
     * Contains the message to be sent
     * @var Message
     */
    private $message;

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
            $bitString .= $this->getPacketIdentifierBinaryRepresentation();
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

    /**
     * @return string
     * @throws \unreal4u\MQTT\Exceptions\MissingTopicName
     * @throws \unreal4u\MQTT\Exceptions\MessageTooBig
     * @throws \InvalidArgumentException
     */
    public function createPayload(): string
    {
        if ($this->message === null) {
            throw new \InvalidArgumentException('A message must be set before publishing');
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
     * @param QoSLevel $qoSLevel
     * @return Publish
     * @throws \unreal4u\MQTT\Exceptions\InvalidQoSLevel
     */
    private function analyzeFirstByte(int $firstByte, QoSLevel $qoSLevel): Publish
    {
        $this->logger->debug('Analyzing first byte', [sprintf('%08d', decbin($firstByte))]);
        // Retained bit is bit 0 of first byte
        $this->message->setRetainFlag(false);
        if ($firstByte & 1) {
            $this->logger->debug('Setting retain flag to true');
            $this->message->setRetainFlag(true);
        }
        // QoS level are the last bits 2 & 1 of the first byte
        $this->message->setQoSLevel($qoSLevel);

        // Duplicate message must be checked only on QoS > 0, else set it to false
        $this->isRedelivery = false;
        if ($firstByte & 8 && $this->message->getQoSLevel() !== 0) {
            // Is a duplicate is always bit 3 of first byte
            $this->isRedelivery = true;
            $this->logger->debug('Setting redelivery bit');
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
        $incomingQoSLevel = ($bitString & 4) / 2;
        $this->logger->debug('Setting QoS level', ['incomingQoSLevel' => $incomingQoSLevel]);
        return new QoSLevel($incomingQoSLevel);
    }

    /**
     * Gets the full message in case this object needs to
     *
     * @param string $rawMQTTHeaders
     * @param ClientInterface $client
     * @return string
     * @throws \OutOfBoundsException
     * @throws \InvalidArgumentException
     * @throws \unreal4u\MQTT\Exceptions\MessageTooBig
     * @throws \unreal4u\MQTT\Exceptions\InvalidQoSLevel
     */
    private function completePossibleIncompleteMessage(string $rawMQTTHeaders, ClientInterface $client): string
    {
        if (\strlen($rawMQTTHeaders) === 1) {
            $this->logger->debug('Only one incoming byte, retrieving rest of size and the full payload');
            $restOfBytes = $client->readBrokerData(1);
            $payload = $client->readBrokerData(\ord($restOfBytes));
        } else {
            $this->logger->debug('More than 1 byte detected, calculating and retrieving the rest');
            $restOfBytes = $rawMQTTHeaders{1};
            $payload = substr($rawMQTTHeaders, 2);
            $exactRest = \ord($restOfBytes) - \strlen($payload);
            $payload .= $client->readBrokerData($exactRest);
            $rawMQTTHeaders = $rawMQTTHeaders{0};
        }

        // $rawMQTTHeaders may be redefined
        return $rawMQTTHeaders . $restOfBytes . $payload;
    }

    /**
     * Will perform sanity checks and fill in the Readable object with data
     * @param string $rawMQTTHeaders
     * @param ClientInterface $client
     * @return ReadableContentInterface
     * @throws \unreal4u\MQTT\Exceptions\MessageTooBig
     * @throws \OutOfBoundsException
     * @throws \unreal4u\MQTT\Exceptions\InvalidQoSLevel
     * @throws \InvalidArgumentException
     * @throws \OutOfRangeException
     */
    public function fillObject(string $rawMQTTHeaders, ClientInterface $client): ReadableContentInterface
    {
        $rawMQTTHeaders = $this->completePossibleIncompleteMessage($rawMQTTHeaders, $client);
        #$this->logger->debug('complete headers', ['header' => str2bin($rawMQTTHeaders)]);

        // Topic size is always the 3rd byte
        $firstByte = \ord($rawMQTTHeaders{0});
        $topicSize = \ord($rawMQTTHeaders{3});
        $qosLevel = $this->determineIncomingQoSLevel($firstByte);

        $messageStartPosition = 4;
        if ($qosLevel->getQoSLevel() > 0) {
            $this->logger->debug('QoS level above 0, shifting message start position and getting packet identifier');
            // [2 (fixed header) + 2 (topic size) + $topicSize] marks the beginning of the 2 packet identifier bytes
            $this->setPacketIdentifier(new PacketIdentifier(Utilities::convertBinaryStringToNumber(
                $rawMQTTHeaders{4 + $topicSize} . $rawMQTTHeaders{5 + $topicSize}
            )));
            $this->logger->debug('Determined packet identifier', [
                'PI' => $this->getPacketIdentifier(),
                'firstBit' => \ord($rawMQTTHeaders{4 + $topicSize}),
                'secondBit' => \ord($rawMQTTHeaders{5 + $topicSize})
            ]);
            $messageStartPosition += 2;
        }

        // At this point $rawMQTTHeaders will be always 1 byte long, initialize a Message object with dummy data for now
        $this->message = new Message(
            // Save to assume a constant here: first 2 bytes will always be fixed header, next 2 bytes are topic size
            substr($rawMQTTHeaders, $messageStartPosition + $topicSize),
            new Topic(substr($rawMQTTHeaders, 4, $topicSize))
        );
        $this->analyzeFirstByte(\ord($rawMQTTHeaders{0}), $qosLevel);

        $this->logger->debug('Determined headers', [
            'topicSize' => $topicSize,
            'QoSLevel' => $this->message->getQoSLevel(),
            'isDuplicate' => $this->isRedelivery,
            'isRetained' => $this->message->isRetained(),
            'packetIdentifier' => $this->packetIdentifier->getPacketIdentifierValue(),
        ]);


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
                $client->processObject($this->composePubAckAnswer());
            } elseif ($qosLevel === 2) {
                $this->logger->debug('Responding with PubRec', ['qosLevel' => $qosLevel]);
                $client->processObject($this->composePubRecAnswer());
            }
        }

        return true;
    }

    private function composePubRecAnswer(): PubRec
    {
        $pubRec = new PubRec($this->logger);
        $pubRec->setPacketIdentifier($this->packetIdentifier);
        return $pubRec;
    }

    /**
     * Composes a PubAck answer with the same packetIdentifier as what we received
     * @return PubAck
     */
    private function composePubAckAnswer(): PubAck
    {
        $pubAck = new PubAck($this->logger);
        $pubAck->setPacketIdentifier($this->packetIdentifier);
        return $pubAck;
    }

    /**
     * PUBLISH packet is the exception to the rule: it is not started on base of a packet that gets sent by us
     */
    public function getOriginControlPacket(): int
    {
        return 0;
    }
}
