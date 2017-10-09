<?php

declare(strict_types=1);

namespace unreal4u\MQTT\Protocol;

use unreal4u\MQTT\Application\EmptyReadableResponse;
use unreal4u\MQTT\Internals\CommonFunctionality;
use unreal4u\MQTT\Internals\ReadableContentInterface;
use unreal4u\MQTT\Internals\WritableBase;
use unreal4u\MQTT\Internals\WritableContentInterface;

final class Disconnect implements WritableContentInterface
{
    use CommonFunctionality;
    use WritableBase;

    const CONTROL_PACKET_VALUE = 14;

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
        return new EmptyReadableResponse($this->logger);
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
