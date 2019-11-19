<?php

declare(strict_types=1);

namespace unreal4u\MQTT\Protocol;

use unreal4u\MQTT\Internals\ClientInterface;
use unreal4u\MQTT\Internals\ProtocolBase;
use unreal4u\MQTT\Internals\DisconnectCleanup;
use unreal4u\MQTT\Internals\ReadableContentInterface;
use unreal4u\MQTT\Internals\WritableContent;
use unreal4u\MQTT\Internals\WritableContentInterface;

/**
 * The DISCONNECT Packet is the final Control Packet sent from the Client to the Server.
 *
 * It indicates that the Client is disconnecting cleanly.
 */
final class Disconnect extends ProtocolBase implements WritableContentInterface
{
    use /** @noinspection TraitsPropertiesConflictsInspection */
        WritableContent;

    private const CONTROL_PACKET_VALUE = 14;

    public function createVariableHeader(): string
    {
        return '';
    }

    public function createPayload(): string
    {
        return '';
    }

    public function expectAnswer(string $brokerBitStream, ClientInterface $client): ReadableContentInterface
    {
        return new DisconnectCleanup($this->logger);
    }

    /**
     * A disconnect should never expect an answer back
     * @return bool
     */
    public function shouldExpectAnswer(): bool
    {
        return false;
    }
}
