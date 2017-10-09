<?php

declare(strict_types=1);

namespace unreal4u\MQTT\Protocol;

use unreal4u\MQTT\Client;
use unreal4u\MQTT\Internals\CommonFunctionality;
use unreal4u\MQTT\Internals\ReadableContent;
use unreal4u\MQTT\Internals\ReadableContentInterface;

final class PingResp implements ReadableContentInterface
{
    use CommonFunctionality;
    use ReadableContent;

    const CONTROL_PACKET_VALUE = 13;

    public function fillObject(): ReadableContentInterface
    {
        return $this;
    }

    public function performSpecialActions(Client $client): bool
    {
        $this->logger->debug('Updating internal last communication', ['object' => get_class($this)]);
        $client->updateLastCommunication();
        return true;
    }
}
