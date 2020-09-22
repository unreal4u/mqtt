<?php

declare(strict_types=1);

namespace unreal4u\MQTT\Protocol;

use InvalidArgumentException;
use OutOfBoundsException;
use OutOfRangeException;
use unreal4u\MQTT\Application\EmptyReadableResponse;
use unreal4u\MQTT\DataTypes\Message;
use unreal4u\MQTT\DataTypes\PacketIdentifier;
use unreal4u\MQTT\DataTypes\QoSLevel;
use unreal4u\MQTT\DataTypes\TopicName;
use unreal4u\MQTT\Exceptions\Connect\NoConnectionParametersDefined;
use unreal4u\MQTT\Exceptions\InvalidQoSLevel;
use unreal4u\MQTT\Exceptions\InvalidRequest;
use unreal4u\MQTT\Exceptions\InvalidResponseType;
use unreal4u\MQTT\Exceptions\MessageTooBig;
use unreal4u\MQTT\Exceptions\MissingTopicName;
use unreal4u\MQTT\Exceptions\NotConnected;
use unreal4u\MQTT\Internals\ClientInterface;
use unreal4u\MQTT\Internals\PacketIdentifierFunctionality;
use unreal4u\MQTT\Internals\ProtocolBase;
use unreal4u\MQTT\Internals\ReadableContent;
use unreal4u\MQTT\Internals\ReadableContentInterface;
use unreal4u\MQTT\Internals\WritableContent;
use unreal4u\MQTT\Internals\WritableContentInterface;
use unreal4u\MQTT\Utilities;

use function decbin;
use function ord;
use function sprintf;
use function strlen;
use function substr;

/**
 * A PUBLISH Control Packet is sent from a Client to a Server or vice-versa to transport an Application Message.
 *
 * @see http://docs.oasis-open.org/mqtt/mqtt/v3.1.1/os/mqtt-v3.1.1-os.html#_Toc398718037
 *
 * QoS lvl1:
 *   First packet: PUBLISH
 *   Second packet: PUBACK
 *
 * QoS lvl2:
 *   First packet: PUBLISH
 *   Second packet: PUBREC
 *   Third packet: PUBREL
 *   Fourth packet: PUBCOMP
 *
 * @see https://go.gliffy.com/go/publish/12498076
 */
final class Publish extends ProtocolBase implements ReadableContentInterface, WritableContentInterface
{
    use ReadableContent;
    use /** @noinspection TraitsPropertiesConflictsInspection */
        WritableContent;
    use PacketIdentifierFunctionality;

    private const CONTROL_PACKET_VALUE = 3;

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
     * @throws InvalidQoSLevel
     * @throws MissingTopicName
     * @throws OutOfRangeException
     * @throws InvalidArgumentException
     */
    public function createVariableHeader(): string
    {
        if ($this->message === null) {
            throw new InvalidArgumentException('You must at least provide a message object with a topic name');
        }

        $variableHeaderContents = $this->createUTF8String($this->message->getTopicName());
        // Reset the special flags should the same object be reused with another message
        $this->specialFlags = 0;

        $variableHeaderContents .= $this->createVariableHeaderFlags();

        return $variableHeaderContents;
    }

    /**
     * Sets some common flags and returns the variable header string should there be one
     *
     * @return string
     * @throws OutOfRangeException
     * @throws InvalidQoSLevel
     */
    private function createVariableHeaderFlags(): string
    {
        if ($this->isRedelivery) {
            // DUP flag: if the message is a re-delivery, mark it as such
            $this->specialFlags |= 8;
            $this->logger->debug('Activating redelivery bit');
        }

        if ($this->message->isRetained()) {
            // RETAIN flag: should the server retain the message?
            $this->specialFlags |= 1;
            $this->logger->debug('Activating retain flag');
        }

        // Check QoS level and perform the corresponding actions
        if ($this->message->getQoSLevel() !== 0) {
            // 0 for QoS lvl2 for QoS lvl1 and 4 for QoS lvl2
            $this->specialFlags |= ($this->message->getQoSLevel() * 2);
            $this->logger->debug(sprintf('Activating QoS level %d bit', $this->message->getQoSLevel()));
            return $this->getPacketIdentifierBinaryRepresentation();
        }

        return '';
    }

    /**
     * @return string
     * @throws MissingTopicName
     * @throws MessageTooBig
     * @throws InvalidArgumentException
     */
    public function createPayload(): string
    {
        if ($this->message === null) {
            throw new InvalidArgumentException('A message must be set before publishing');
        }
        return $this->message->getPayload();
    }

    /**
     * QoS level 0 does not have to wait for a answer, so return false. Any other QoS level returns true
     * @return bool
     * @throws InvalidQoSLevel
     */
    public function shouldExpectAnswer(): bool
    {
        $shouldExpectAnswer = !($this->message->getQoSLevel() === 0);
        $this->logger->debug('Checking whether we should expect an answer or not', [
            'shouldExpectAnswer' => $shouldExpectAnswer,
        ]);
        return $shouldExpectAnswer;
    }

    /**
     * @param string $brokerBitStream
     * @param ClientInterface $client
     * @return ReadableContentInterface
     * @throws InvalidResponseType
     */
    public function expectAnswer(string $brokerBitStream, ClientInterface $client): ReadableContentInterface
    {
        switch ($this->message->getQoSLevel()) {
            case 1:
                $pubAck = new PubAck($this->logger);
                $pubAck->instantiateObject($brokerBitStream, $client);
                return $pubAck;
            case 2:
                $pubRec = new PubRec($this->logger);
                $pubRec->instantiateObject($brokerBitStream, $client);
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
     * @throws InvalidQoSLevel
     */
    private function analyzeFirstByte(int $firstByte, QoSLevel $qoSLevel): Publish
    {
        $this->logger->debug('Analyzing first byte', [sprintf('%08d', decbin($firstByte))]);
        // Retained bit is bit 0 of first byte
        $this->message->setRetainFlag(false);
        if (($firstByte & 1) === 1) {
            $this->logger->debug('Setting retain flag to true');
            $this->message->setRetainFlag(true);
        }
        // QoS level is already been taken care of, assign it to the message at this point
        $this->message->setQoSLevel($qoSLevel);

        // Duplicate message must be checked only on QoS > 0, else set it to false
        $this->isRedelivery = false;
        if (($firstByte & 8) === 8 && $this->message->getQoSLevel() !== 0) {
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
     * @throws InvalidQoSLevel
     */
    private function determineIncomingQoSLevel(int $bitString): QoSLevel
    {
        // QoS lvl are in bit positions 1-2. Shifting is strictly speaking not needed, but increases human comprehension
        $shiftedBits = $bitString >> 1;
        $incomingQoSLevel = 0;
        if (($shiftedBits & 1) === 1) {
            $incomingQoSLevel = 1;
        }
        if (($shiftedBits & 2) === 2) {
            $incomingQoSLevel = 2;
        }

        $this->logger->debug('Setting QoS level', ['bitString' => $bitString, 'incomingQoSLevel' => $incomingQoSLevel]);
        return new QoSLevel($incomingQoSLevel);
    }

    /**
     * Gets the full message in case this object needs to
     *
     * @param string $rawMQTTHeaders
     * @param ClientInterface $client
     * @return string
     * @throws OutOfBoundsException
     * @throws InvalidArgumentException
     * @throws MessageTooBig
     * @throws InvalidQoSLevel
     */
    private function completePossibleIncompleteMessage(string $rawMQTTHeaders, ClientInterface $client): string
    {
        // Read at least one extra byte from the stream if we know that the message is too short
        if (strlen($rawMQTTHeaders) < 2) {
            $rawMQTTHeaders .= $client->readBrokerData(1);
        }

        $restOfBytes = $this->performRemainingLengthFieldOperations($rawMQTTHeaders, $client);

        /*
         * A complete message consists of:
         *  - The very first byte
         *  - The size of the remaining length field (from 1 to 4 bytes)
         *  - The $restOfBytes
         *
         * So we have to compare what we already have vs the above calculation
         *
         * More information:
         * http://docs.oasis-open.org/mqtt/mqtt/v3.1.1/errata01/os/mqtt-v3.1.1-errata01-os-complete.html#_Toc442180832
         */
        if (strlen($rawMQTTHeaders) < ($restOfBytes + $this->sizeOfRemainingLengthField + 1)) {
            // Read only the portion of data we have left from the socket
            $readableDataLeft = ($restOfBytes + $this->sizeOfRemainingLengthField + 1) - strlen($rawMQTTHeaders);
            $rawMQTTHeaders .= $client->readBrokerData($readableDataLeft);
        }

        return $rawMQTTHeaders;
    }

    /**
     * Will perform sanity checks and fill in the Readable object with data
     * @param string $rawMQTTHeaders
     * @param ClientInterface $client
     * @return ReadableContentInterface
     * @throws MessageTooBig
     * @throws OutOfBoundsException
     * @throws InvalidQoSLevel
     * @throws InvalidArgumentException
     * @throws OutOfRangeException
     */
    public function fillObject(string $rawMQTTHeaders, ClientInterface $client): ReadableContentInterface
    {
        // Retrieve full message first
        $fullMessage = $this->completePossibleIncompleteMessage($rawMQTTHeaders, $client);
        // Handy to maintain for debugging purposes
        #$this->logger->debug('Bin data', [\unreal4u\MQTT\DebugTools::convertToBinaryRepresentation($rawMQTTHeaders)]);

        // Handy to have: the first byte
        $firstByte = ord($fullMessage{0});
        // TopicName size is always on the second position after the size of the remaining length field (1 to 4 bytes)
        $topicSize = ord($fullMessage[$this->sizeOfRemainingLengthField + 2]);
        // With the first byte, we can determine the QoS level of the incoming message
        $qosLevel = $this->determineIncomingQoSLevel($firstByte);

        $messageStartPosition = $this->sizeOfRemainingLengthField + 3;
        // If we have a QoS level present, we must retrieve the packet identifier as well
        if ($qosLevel->getQoSLevel() > 0) {
            $this->logger->debug('QoS level above 0, shifting message start position and getting packet identifier');
            // [2 (fixed header) + 2 (topic size) + $topicSize] marks the beginning of the 2 packet identifier bytes
            $this->setPacketIdentifier(new PacketIdentifier(Utilities::convertBinaryStringToNumber(
                $fullMessage[$this->sizeOfRemainingLengthField + 3 + $topicSize] .
                $fullMessage[$this->sizeOfRemainingLengthField + 4 + $topicSize]
            )));
            $this->logger->debug('Determined packet identifier', ['PI' => $this->getPacketIdentifier()]);
            $messageStartPosition += 2;
        }

        // At this point $rawMQTTHeaders will be always 1 byte long, initialize a Message object with dummy data for now
        $this->message = new Message(
            // Save to assume a constant here: first 2 bytes will always be fixed header, next 2 bytes are topic size
            substr($fullMessage, $messageStartPosition + $topicSize),
            new TopicName(substr($fullMessage, $this->sizeOfRemainingLengthField + 3, $topicSize))
        );
        $this->analyzeFirstByte($firstByte, $qosLevel);

        $this->logger->debug('Determined headers', [
            'topicSize' => $topicSize,
            'QoSLevel' => $this->message->getQoSLevel(),
            'isDuplicate' => $this->isRedelivery,
            'isRetained' => $this->message->isRetained(),
            #'packetIdentifier' => $this->packetIdentifier->getPacketIdentifierValue(), // This is not always set!
        ]);

        return $this;
    }

    /**
     * @inheritdoc
     * @throws InvalidRequest
     * @throws InvalidQoSLevel
     * @throws NotConnected
     * @throws NoConnectionParametersDefined
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

    /**
     * Composes a PubRec answer with the same packetIdentifier as what we received
     *
     * @return PubRec
     * @throws InvalidRequest
     */
    private function composePubRecAnswer(): PubRec
    {
        $this->checkForValidPacketIdentifier();
        $pubRec = new PubRec($this->logger);
        $pubRec->setPacketIdentifier($this->packetIdentifier);
        return $pubRec;
    }

    /**
     * Composes a PubAck answer with the same packetIdentifier as what we received
     *
     * @return PubAck
     * @throws InvalidRequest
     */
    private function composePubAckAnswer(): PubAck
    {
        $this->checkForValidPacketIdentifier();
        $pubAck = new PubAck($this->logger);
        $pubAck->setPacketIdentifier($this->packetIdentifier);
        return $pubAck;
    }

    /**
     * Will check whether the current object has a packet identifier set. If not, we are in serious problems!
     *
     * @return Publish
     * @throws InvalidRequest
     */
    private function checkForValidPacketIdentifier(): self
    {
        if ($this->packetIdentifier === null) {
            $this->logger->critical('No valid packet identifier found at a stage where there MUST be one set');
            throw new InvalidRequest('You are trying to send a request without a valid packet identifier');
        }

        return $this;
    }

    /**
     * PUBLISH packet is the exception to the rule: it is not started on base of a packet that gets sent by us
     */
    public function getOriginControlPacket(): int
    {
        return 0;
    }
}
