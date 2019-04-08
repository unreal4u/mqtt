<?php

declare(strict_types=1);

namespace unreal4u\MQTT\Internals;

use unreal4u\MQTT\Exceptions\InvalidResponseType;
use unreal4u\MQTT\Utilities;
use function ord;

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
     * @param string $firstRemainingLengthByte
     * @param ClientInterface $client
     * @param string $rawMQTTHeaders
     * @return int
     */
    private function calculateSizeOfRemainingLengthField(
        string $firstRemainingLengthByte,
        ClientInterface $client,
        string $rawMQTTHeaders = ''
    ): int {
        // Early return: assume defaults if first digit has a value under 128, no further need for complex checks
        if (ord($firstRemainingLengthByte{0}) < 128) {
            return ord($firstRemainingLengthByte{0});
        }

        if (strlen($rawMQTTHeaders) < 4) {
            // If we enter this condition, it is safe to assume that we can at least read 3 more bytes of the stream
            $rawMQTTHeaders .= $client->readBrokerData(3);
        }

        // Estimate how much longer is the remaining length field
        // This will also set $this->sizeOfRemainingLengthField in order to calculate the offset

        // Pass it to our utilities function and return that
        return Utilities::convertRemainingLengthStringToInt($firstRemainingLengthByte);
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
