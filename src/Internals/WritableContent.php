<?php

declare(strict_types=1);

namespace unreal4u\MQTT\Internals;

use Psr\Log\LoggerInterface;
use unreal4u\MQTT\DataTypes\ProtocolVersion;
use unreal4u\MQTT\Exceptions\MessageTooBig;
use unreal4u\MQTT\Utilities;

/**
 * Trait WritableContent
 * @package unreal4u\MQTT\Internals
 */
trait WritableContent
{
    /**
     * @var LoggerInterface
     */
    protected $logger;

    /**
     * Any special flags that are set on runtime
     *
     * PUBLISH for example needs to know QoS, the retain bit and duplicate delivery settings
     * PUBREL, SUBSCRIBE and UNSUBSCRIBE has always bit 1 set to true
     *
     * @var int
     */
    protected $specialFlags = 0;

    /**
     * The protocol version we are talking with. Currently only v3.1.1 is supported
     * @var string
     */
    public $protocolLevel = '3.1.1';

    /**
     * @var ProtocolVersion
     */
    private $protocolVersion;

    /**
     * Returns the fixed header part needed for all methods
     *
     * This takes into account the basic control packet value, any special flags and, in the second byte, the variable
     * header length
     *
     * @param int $variableHeaderLength
     * @return string
     * @throws \unreal4u\MQTT\Exceptions\MessageTooBig
     */
    final public function createFixedHeader(int $variableHeaderLength): string
    {
        $this->logger->debug('Creating fixed header with values', [
            'controlPacketValue' => static::CONTROL_PACKET_VALUE,
            'specialFlags' => $this->specialFlags,
            'variableHeaderLength' => $variableHeaderLength,
            'composed' => decbin(\chr((static::CONTROL_PACKET_VALUE << 4) | $this->specialFlags)),
        ]);

        // Binary OR is safe to do because the first 4 bits are always 0 after shifting
        return
            \chr((static::CONTROL_PACKET_VALUE << 4) | $this->specialFlags) .
            $this->getRemainingLength($variableHeaderLength);
    }

    /**
     * Returns the correct format for the length in bytes of the remaining bytes
     *
     * @param int $lengthInBytes
     * @return string
     * @throws \unreal4u\MQTT\Exceptions\MessageTooBig
     */
    final public function getRemainingLength(int $lengthInBytes): string
    {
        if ($lengthInBytes > 268435455) {
            throw new MessageTooBig('The message cannot exceed 268435455 bytes in length');
        }

        $x = $lengthInBytes;
        $outputString = '';
        do {
            $encodedByte = $x % 128;
            $x >>= 7; // Shift 7 bytes
            // if there are more data to encode, set the top bit of this byte
            if ($x > 0) {
                $encodedByte |= 128;
            }
            $outputString .= \chr($encodedByte);
        } while ($x > 0);

        return $outputString;
    }

    /**
     * Creates the entire message
     * @return string
     * @throws \unreal4u\MQTT\Exceptions\MessageTooBig
     */
    final public function createSendableMessage(): string
    {
        $variableHeader = $this->createVariableHeader();
        $this->logger->debug('Created variable header', ['variableHeader' => base64_encode($variableHeader)]);
        $payload = $this->createPayload();
        $this->logger->debug('Created payload', ['payload' => base64_encode($payload)]);
        $fixedHeader = $this->createFixedHeader(\strlen($variableHeader . $payload));
        $this->logger->debug('Created fixed header', ['fixedHeader' => base64_encode($fixedHeader)]);

        return $fixedHeader . $variableHeader . $payload;
    }

    final public function setProtocolVersion(ProtocolVersion $protocolVersion): self
    {
        $this->protocolVersion = $protocolVersion;
        return $this;
    }

    /**
     * Creates a UTF8 big-endian representation of the given string
     *
     * @param string $data
     * @return string
     * @throws \OutOfRangeException
     */
    final public function createUTF8String(string $data): string
    {
        $returnString = '';
        if ($data !== '') {
            $returnString = Utilities::convertNumberToBinaryString(\strlen($data)) . $data;
        }

        return $returnString;
    }

    /**
     * Will return an object of the type the broker has returned to us
     *
     * @param string $data
     * @param ClientInterface $client
     *
     * @return ReadableContentInterface
     * @throws \DomainException
     */
    public function expectAnswer(string $data, ClientInterface $client): ReadableContentInterface
    {
        $this->logger->info('String of incoming data confirmed, returning new object', ['callee' => \get_class($this)]);

        $eventManager = new EventManager($this->logger);
        return $eventManager->analyzeHeaders($data, $client);
    }

    /**
     * Gets the control packet value for this object
     *
     * @return int
     */
    final public static function getControlPacketValue(): int
    {
        return static::CONTROL_PACKET_VALUE;
    }
}
