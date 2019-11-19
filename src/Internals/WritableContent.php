<?php

declare(strict_types=1);

namespace unreal4u\MQTT\Internals;

use DomainException;
use OutOfRangeException;
use Psr\Log\LoggerInterface;
use unreal4u\MQTT\Exceptions\MessageTooBig;
use unreal4u\MQTT\Utilities;

use function base64_encode;
use function chr;
use function decbin;
use function get_class;
use function strlen;

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
     * Returns the fixed header part needed for all methods
     *
     * This takes into account the basic control packet value, any special flags and, in the second byte, the variable
     * header length
     *
     * @param int $variableHeaderLength
     * @return string
     * @throws MessageTooBig
     */
    final public function createFixedHeader(int $variableHeaderLength): string
    {
        $this->logger->debug('Creating fixed header with values', [
            'controlPacketValue' => self::getControlPacketValue(),
            'specialFlags' => $this->specialFlags,
            'variableHeaderLength' => $variableHeaderLength,
            'composed' => decbin(chr((self::getControlPacketValue() << 4) | $this->specialFlags)),
        ]);

        // Binary OR is safe to do because the first 4 bits are always 0 after shifting
        return
            chr((self::getControlPacketValue() << 4) | $this->specialFlags) .
            Utilities::formatRemainingLengthOutput($variableHeaderLength);
    }

    /**
     * Creates the entire message
     * @return string
     * @throws MessageTooBig
     */
    final public function createSendableMessage(): string
    {
        $variableHeader = $this->createVariableHeader();
        $this->logger->debug('Created variable header', ['variableHeader' => base64_encode($variableHeader)]);
        $payload = $this->createPayload();
        $this->logger->debug('Created payload', ['payload' => base64_encode($payload)]);
        $fixedHeader = $this->createFixedHeader(strlen($variableHeader . $payload));
        $this->logger->debug('Created fixed header', ['fixedHeader' => base64_encode($fixedHeader)]);

        return $fixedHeader . $variableHeader . $payload;
    }

    /**
     * Creates the variable header that each method has
     *
     * @return string
     */
    abstract public function createVariableHeader(): string;

    /**
     * Creates the actual payload to be sent
     *
     * @return string
     */
    abstract public function createPayload(): string;

    /**
     * Creates a UTF8 big-endian representation of the given string
     *
     * @param string $nonFormattedString
     * @return string
     * @throws OutOfRangeException
     */
    final public function createUTF8String(string $nonFormattedString): string
    {
        $returnString = '';
        if ($nonFormattedString !== '') {
            $returnString = Utilities::convertNumberToBinaryString(strlen($nonFormattedString)) . $nonFormattedString;
        }

        return $returnString;
    }

    /**
     * Will return an object of the type the broker has returned to us
     *
     * @param string $brokerBitStream
     * @param ClientInterface $client
     *
     * @return ReadableContentInterface
     * @throws DomainException
     */
    public function expectAnswer(string $brokerBitStream, ClientInterface $client): ReadableContentInterface
    {
        $this->logger->info('String of incoming data confirmed, returning new object', ['callee' => get_class($this)]);

        $eventManager = new EventManager($this->logger);
        return $eventManager->analyzeHeaders($brokerBitStream, $client);
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
