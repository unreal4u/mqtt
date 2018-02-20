<?php

declare(strict_types=1);

namespace unreal4u\MQTT\Protocol;

use unreal4u\MQTT\Internals\ClientInterface;
use unreal4u\MQTT\Internals\ProtocolBase;
use unreal4u\MQTT\Internals\ReadableContent;
use unreal4u\MQTT\Internals\ReadableContentInterface;
use unreal4u\MQTT\Internals\WritableContentInterface;

/**
 * A PINGRESP Packet is sent by the Server to the Client in response to a PINGREQ Packet.
 *
 * It indicates that the Server is alive.
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
        $client->updateLastCommunication();
        return true;
    }

    /**
     * @inheritdoc
     */
    public function originPacketIdentifier(): int
    {
        return PingReq::getControlPacketValue();
    }
}
