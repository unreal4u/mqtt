<?php

declare(strict_types=1);

namespace unreal4u\MQTT\Internals;

use unreal4u\MQTT\Exceptions\InvalidResponseType;
use unreal4u\MQTT\Utilities;

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

    final public function instantiateObject(string $rawMQTTHeaders): ReadableContentInterface
    {
        //var_dump(base64_encode($rawMQTTHeaders)); // For now: make it a bit easier to create unit tests
        $this
            ->checkControlPacketValue(\ord($rawMQTTHeaders[0]) >> 4)
            ->fillObject($rawMQTTHeaders);

        return $this;
    }

    final public function checkControlPacketValue(int $controlPacketValue): ReadableContentInterface
    {
        // Check whether the first byte corresponds to the expected control packet value
        if (static::CONTROL_PACKET_VALUE !== $controlPacketValue) {
            throw new InvalidResponseType(sprintf(
                'Value of received value does not correspond to response (Expected: %d, Actual: %d)',
                static::CONTROL_PACKET_VALUE,
                $controlPacketValue
            ));
        }

        return $this;
    }

    /**
     * Extracts the packet identifier from the raw headers
     *
     * @param string $rawMQTTHeaders
     * @return int
     * @throws \OutOfRangeException
     */
    private function extractPacketIdentifier(string $rawMQTTHeaders): int
    {
        return Utilities::convertBinaryStringToNumber($rawMQTTHeaders{2} . $rawMQTTHeaders{3});
    }

    /**
     * Any class can overwrite the default behaviour
     * @param string $rawMQTTHeaders
     * @return ReadableContentInterface
     */
    public function fillObject(string $rawMQTTHeaders): ReadableContentInterface
    {
        return $this;
    }

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
