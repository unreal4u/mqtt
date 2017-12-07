<?php

declare(strict_types=1);

namespace unreal4u\MQTT\Protocol;

use unreal4u\MQTT\Client;
use unreal4u\MQTT\Internals\ProtocolBase;
use unreal4u\MQTT\Internals\ReadableContent;
use unreal4u\MQTT\Internals\ReadableContentInterface;
use unreal4u\MQTT\Internals\WritableContentInterface;

final class SubAck extends ProtocolBase implements ReadableContentInterface
{
    use ReadableContent;

    const CONTROL_PACKET_VALUE = 9;

    /**
     * @inheritdoc
     */
    public function performSpecialActions(Client $client, WritableContentInterface $originalRequest): bool
    {
        $client
            ->updateLastCommunication()
            ->setBlocking(false)
        ;
        return true;
    }
}
