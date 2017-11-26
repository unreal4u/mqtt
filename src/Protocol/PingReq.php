<?php

declare(strict_types=1);

namespace unreal4u\MQTT\Protocol;

use unreal4u\MQTT\Internals\CommonFunctionality;
use unreal4u\MQTT\Internals\ReadableContentInterface;
use unreal4u\MQTT\Internals\WritableContent;
use unreal4u\MQTT\Internals\WritableContentInterface;

final class PingReq implements WritableContentInterface
{
    use CommonFunctionality;
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

    public function expectAnswer(string $data): ReadableContentInterface
    {
        $this->logger->info('String of incoming data confirmed, returning new object', ['class' => \get_class($this)]);
        $pingResp = new PingResp($this->logger);
        $pingResp->populate($data);
        return $pingResp;
    }

    public function shouldExpectAnswer(): bool
    {
        return true;
    }
}
