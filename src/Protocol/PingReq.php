<?php

declare(strict_types=1);

namespace unreal4u\MQTT\Protocol;

use unreal4u\MQTT\Internals\ProtocolBase;
use unreal4u\MQTT\Internals\WritableContent;
use unreal4u\MQTT\Internals\WritableContentInterface;

final class PingReq extends ProtocolBase implements WritableContentInterface
{
    use WritableContent;

    const CONTROL_PACKET_VALUE = 12;

    public function createVariableHeader(): string
    {
        return '';
    }

    public function createPayload(): string
    {
        return '';
    }

    public function shouldExpectAnswer(): bool
    {
        return true;
    }
}
