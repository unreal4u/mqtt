<?php

declare(strict_types=1);

namespace unreal4u\MQTT\Internals;

use Psr\Log\LoggerInterface;
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
        $this->logger->debug('Creating variable header', ['variableHeader' => base64_encode($variableHeader)]);
        $payload = $this->createPayload();
        $this->logger->debug('Creating payload', ['payload' => base64_encode($payload)]);
        $fixedHeader = $this->createFixedHeader(mb_strlen($variableHeader . $payload));
        $this->logger->debug('Created fixed header', ['fixedHeader' => base64_encode($fixedHeader)]);

        return $fixedHeader . $variableHeader . $payload;
    }

    /**
     * Gets the current protocol lvl bit
     * @return string
     */
    final public function getProtocolLevel(): string
    {
        if ($this->protocolLevel === '3.1.1') {
            return \chr(4);
        }

        // Return a default of 0, which will be invalid anyway (but data will be sent to the broker this way)
        return \chr(0);
    }

    /**
     * Creates a UTF8 big-endian representation of the given string
     *
     * @param string $data
     * @return string
     */
    final public function createUTF8String(string $data): string
    {
        return Utilities::convertNumberToBinaryString(mb_strlen($data)) . $data;
    }
}
