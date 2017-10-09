<?php

declare(strict_types=1);

namespace unreal4u\MQTT\Protocol;

use unreal4u\MQTT\Client;
use unreal4u\MQTT\Internals\CommonFunctionality;
use unreal4u\MQTT\Internals\ReadableBase;
use unreal4u\MQTT\Internals\ReadableContentInterface;

final class SubAck implements ReadableContentInterface
{
    use CommonFunctionality;
    use ReadableBase;

    const CONTROL_PACKET_VALUE = 9;

    public function fillObject(): ReadableContentInterface
    {
        return $this;
    }

    public function performSpecialActions(Client $client): bool
    {
        $client->updateLastCommunication();
        $client->setBlocking(false);
        return true;
    }
}
