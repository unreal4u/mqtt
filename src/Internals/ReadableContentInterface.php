<?php

declare(strict_types=1);

namespace unreal4u\MQTT\Internals;

use Psr\Log\LoggerInterface;
use unreal4u\MQTT\Exceptions\UnmatchingPacketIdentifiers;

interface ReadableContentInterface
{
    public function __construct(LoggerInterface $logger = null);

    /**
     * Populates the object and performs some basic checks on everything
     *
     * @param string $rawMQTTHeaders
     * @param ClientInterface $client
     * @return bool
     */
    public function instantiateObject(string $rawMQTTHeaders, ClientInterface $client): bool;

    /**
     * Will perform sanity checks and fill in the Readable object with data
     * @param string $rawMQTTHeaders
     * @param ClientInterface $client
     * @return ReadableContentInterface
     */
    public function fillObject(string $rawMQTTHeaders, ClientInterface $client): ReadableContentInterface;

    /**
     * Some operations require setting some things in the client or perform some checks, this hook will allow just that
     *
     * @param ClientInterface $client
     * @param WritableContentInterface $originalRequest Will be used to validate stuff such as packetIdentifier
     *
     * @return bool
     * @throws UnmatchingPacketIdentifiers
     */
    public function performSpecialActions(ClientInterface $client, WritableContentInterface $originalRequest): bool;
}
