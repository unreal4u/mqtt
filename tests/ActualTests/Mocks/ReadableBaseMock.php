<?php

declare(strict_types=1);

namespace tests\unreal4u\MQTT\Mocks;

use unreal4u\MQTT\Client;
use unreal4u\MQTT\Internals\CommonFunctionality;
use unreal4u\MQTT\Internals\ReadableContentInterface;

class ReadableBaseMock implements ReadableContentInterface
{
    use CommonFunctionality;

    /**
     * Will perform sanity checks and fill in the Readable object with data
     * @return ReadableContentInterface
     */
    public function fillObject(): ReadableContentInterface
    {
        return $this;
    }

    /**
     * Some operations require setting some things in the client, this hook will do so
     *
     * @param Client $client
     * @return bool
     */
    public function performSpecialActions(Client $client): bool
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
