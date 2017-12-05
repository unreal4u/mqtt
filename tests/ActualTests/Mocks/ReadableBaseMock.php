<?php

declare(strict_types=1);

namespace tests\unreal4u\MQTT\Mocks;

use unreal4u\MQTT\Client;
use unreal4u\MQTT\Internals\ProtocolBase;
use unreal4u\MQTT\Internals\ReadableContentInterface;
use unreal4u\MQTT\Internals\WritableContentInterface;

class ReadableBaseMock extends ProtocolBase implements ReadableContentInterface
{
    /**
     * Will perform sanity checks and fill in the Readable object with data
     * @return ReadableContentInterface
     */
    public function fillObject(): ReadableContentInterface
    {
        return $this;
    }

    /**
     * @inheritdoc
     */
    public function performSpecialActions(Client $client, WritableContentInterface $originalRequest): bool
    {
        return true;
    }

    /**
     * Populates the object and performs some basic checks on everything
     *
     * @param string $rawMQTTHeaders
     * @return ReadableContentInterface
     */
    public function populate(string $rawMQTTHeaders): ReadableContentInterface
    {
        return $this;
    }

    /**
     * Checks whether the response from the MQTT protocol corresponds to the object we're trying to initialize
     * @return ReadableContentInterface
     */
    public function checkControlPacketValue(): ReadableContentInterface
    {
        // TODO: Implement checkControlPacketValue() method.
    }
}
