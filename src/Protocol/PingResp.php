<?php

declare(strict_types=1);

namespace unreal4u\MQTT\Protocol;

use unreal4u\MQTT\Internals\ClientInterface;
use unreal4u\MQTT\Internals\ProtocolBase;
use unreal4u\MQTT\Internals\ReadableContent;
use unreal4u\MQTT\Internals\ReadableContentInterface;
use unreal4u\MQTT\Internals\WritableContentInterface;

/**
 * Class PingResp
 * @package unreal4u\MQTT\Protocol
 */
final class PingResp extends ProtocolBase implements ReadableContentInterface
{
    use ReadableContent;

    const CONTROL_PACKET_VALUE = 13;

    /**
     * @inheritdoc
     */
    public function performSpecialActions(ClientInterface $client, WritableContentInterface $originalRequest): bool
    {
        $this->logger->debug('Updating internal last communication', ['object' => \get_class($this)]);
        $client->updateLastCommunication();
        return true;
    }
}
