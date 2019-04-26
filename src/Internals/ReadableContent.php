<?php

declare(strict_types=1);

namespace unreal4u\MQTT\Internals;

use unreal4u\MQTT\Exceptions\InvalidResponseType;
use unreal4u\MQTT\Utilities;
use function ord;
use function strlen;

/**
 * Trait ReadableContent
 * @package unreal4u\MQTT\Internals
 */
trait ReadableContent
{
    /**
     * Length of variable header
     * @var int
     */
    protected $variableHeaderSize = 0;

    /**
     * The remaining length field may be from 1 to 4 bytes long, this field will represent that offset
     * @var int
     */
    private $sizeOfRemainingLengthField = 1;

    /**
     * @param string $rawMQTTHeaders
     * @param ClientInterface $client
     * @return bool
     * @throws InvalidResponseType
     */
    final public function instantiateObject(string $rawMQTTHeaders, ClientInterface $client): bool
    {
        //var_dump(base64_encode($rawMQTTHeaders)); // Make it a bit easier to create unit tests
        $this->checkControlPacketValue(ord($rawMQTTHeaders[0]) >> 4);
        $this->fillObject($rawMQTTHeaders, $client);

        return true;
    }

    /**
     * Checks whether the control packet corresponds to this object
     *
     * @param int $controlPacketValue
     * @return bool
     * @throws InvalidResponseType
     */
    private function checkControlPacketValue(int $controlPacketValue): bool
    {
        // Check whether the first byte corresponds to the expected control packet value
        if (static::CONTROL_PACKET_VALUE !== $controlPacketValue) {
            throw new InvalidResponseType(sprintf(
                'Value of received value does not correspond to response (Expected: %d, Actual: %d)',
                static::CONTROL_PACKET_VALUE,
                $controlPacketValue
            ));
        }

        return true;
    }

    /**
     * Returns the correct format for the length in bytes of the remaining bytes
     *
     * @param ClientInterface $client
     * @param string $rawMQTTHeaders
     * @return int
     */
    private function performRemainingLengthFieldOperations(
        string &$rawMQTTHeaders,
        ClientInterface $client
    ): int {
        // Early return: assume defaults if first digit has a value under 128, no further need for complex checks
        if (ord($rawMQTTHeaders{0}) < 128) {
            return ord($rawMQTTHeaders{0});
        }

        // If we have less than 4 bytes now, we should really try to recover the rest of the remaining field data
        if (strlen($rawMQTTHeaders) < 4) {
            // At this point we could actually read at least 128 as a minimum, but restrict it to what we need right now
            $rawMQTTHeaders .= $client->readBrokerData(4 - strlen($rawMQTTHeaders));
        }

        $remainingBytes = Utilities::convertRemainingLengthStringToInt($rawMQTTHeaders);

        // Estimate how much longer is the remaining length field, this will also set $this->sizeOfRemainingLengthField
        $this->calculateSizeOfRemainingLengthField($remainingBytes);
        return $remainingBytes;
    }

    /**
     * Sets the offset of the remaining length field
     *
     * @param int $size
     * @return int
     */
    private function calculateSizeOfRemainingLengthField(int $size): int
    {
        $blockSize = $iterations = 0;
        while ($size >= $blockSize) {
            $iterations++;
            $blockSize = 128 ** $iterations;
        }

        $this->sizeOfRemainingLengthField = $iterations;
        return $iterations;
    }

    /**
     * All classes must implement how to handle the object filling
     * @param string $rawMQTTHeaders
     * @param ClientInterface $client
     * @return ReadableContentInterface
     */
    abstract public function fillObject(string $rawMQTTHeaders, ClientInterface $client): ReadableContentInterface;

    /**
     * Any class can overwrite the default behaviour
     * @param ClientInterface $client
     * @param WritableContentInterface $originalRequest
     * @return bool
     */
    public function performSpecialActions(ClientInterface $client, WritableContentInterface $originalRequest): bool
    {
        return false;
    }
}
