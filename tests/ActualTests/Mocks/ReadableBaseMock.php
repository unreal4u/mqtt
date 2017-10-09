<?php

declare(strict_types=1);

namespace tests\unreal4u\MQTT\Mocks;

use unreal4u\MQTT\Client;
use unreal4u\MQTT\Protocol\ReadableBase;
use unreal4u\MQTT\Protocol\ReadableContentInterface;

class ReadableBaseMock extends ReadableBase
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
     * Some operations require setting some things in the client, this hook will do so
     *
     * @param Client $client
     * @return bool
     */
    public function performSpecialActions(Client $client): bool
    {
        return true;
    }
}
