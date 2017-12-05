<?php

declare(strict_types=1);

namespace unreal4u\MQTT\Internals;

use Psr\Log\LoggerInterface;
use unreal4u\MQTT\Client;

interface ReadableContentInterface
{
    public function __construct(LoggerInterface $logger = null);

    /**
     * Populates the object and performs some basic checks on everything
     *
     * @param string $rawMQTTHeaders
     * @return ReadableContentInterface
     */
    public function populate(string $rawMQTTHeaders): ReadableContentInterface;

    /**
     * Checks whether the response from the MQTT protocol corresponds to the object we're trying to initialize
     * @return ReadableContentInterface
     */
    public function checkControlPacketValue(): ReadableContentInterface;

    /**
     * Will perform sanity checks and fill in the Readable object with data
     * @return ReadableContentInterface
     */
    public function fillObject(): ReadableContentInterface;

    /**
     * Some operations require setting some things in the client, this hook will do so
     *
     * @param Client $client
     * @param WritableContentInterface $originalRequest Will be used to validate stuff such as packetIdentifier
     *
     * @return bool
     * @throws \unreal4u\MQTT\Exceptions\ServerClosedConnection
     * @throws \unreal4u\MQTT\Exceptions\NotConnected
     * @throws \unreal4u\MQTT\Exceptions\Connect\NoConnectionParametersDefined
     */
    public function performSpecialActions(Client $client, WritableContentInterface $originalRequest): bool;
}
