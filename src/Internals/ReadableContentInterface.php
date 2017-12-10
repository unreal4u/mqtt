<?php

declare(strict_types=1);

namespace unreal4u\MQTT\Internals;

use Psr\Log\LoggerInterface;

interface ReadableContentInterface
{
    public function __construct(LoggerInterface $logger = null);

    /**
     * Populates the object and performs some basic checks on everything
     *
     * @param string $rawMQTTHeaders
     * @return ReadableContentInterface
     */
    public function instantiateObject(string $rawMQTTHeaders): ReadableContentInterface;

    /**
     * Checks whether the response from the MQTT protocol corresponds to the object we're trying to initialize
     * @param int $packetControlValue
     * @return ReadableContentInterface
     */
    public function checkControlPacketValue(int $packetControlValue): ReadableContentInterface;

    /**
     * Will perform sanity checks and fill in the Readable object with data
     * @param string $rawMQTTHeaders
     * @return ReadableContentInterface
     */
    public function fillObject(string $rawMQTTHeaders): ReadableContentInterface;

    /**
     * Some operations require setting some things in the client or perform some checks, this hook will allow just that
     *
     * @param ClientInterface $client
     * @param WritableContentInterface $originalRequest Will be used to validate stuff such as packetIdentifier
     *
     * @return bool
     */
    public function performSpecialActions(ClientInterface $client, WritableContentInterface $originalRequest): bool;
}
