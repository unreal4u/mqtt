<?php

declare(strict_types=1);

namespace unreal4u\MQTT\Protocol;

use unreal4u\MQTT\Internals\ClientInterface;
use unreal4u\MQTT\Internals\ProtocolBase;
use unreal4u\MQTT\Internals\DisconnectCleanup;
use unreal4u\MQTT\Internals\ReadableContentInterface;
use unreal4u\MQTT\Internals\WritableContent;
use unreal4u\MQTT\Internals\WritableContentInterface;

final class Disconnect extends ProtocolBase implements WritableContentInterface
{
    use WritableContent;

    const CONTROL_PACKET_VALUE = 14;

    public function createVariableHeader(): string
    {
        return '';
    }

    public function createPayload(): string
    {
        return '';
    }

    public function expectAnswer(string $data, ClientInterface $client): ReadableContentInterface
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
