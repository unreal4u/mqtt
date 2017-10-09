<?php

declare(strict_types=1);

namespace tests\unreal4u\MQTT\Mocks;

use unreal4u\MQTT\Protocol\Connack;
use unreal4u\MQTT\Protocol\ReadableContentInterface;
use unreal4u\MQTT\Protocol\WritableBase;

class WritableBaseMock extends WritableBase
{

    /**
     * Creates the variable header that each method has
     * @return string
     */
    public function createVariableHeader(): string
    {
        return '';
    }

    /**
     * Creates the actual payload to be sent
     * @return string
     */
    public function createPayload(): string
    {
        return '';
    }

    /**
     * What specific kind of post we should expect back from this request
     *
     * @param string $data
     * @return ReadableContentInterface
     */
    public function expectAnswer(string $data): ReadableContentInterface
    {
        return new Connack($data);
    }

    /**
     * Some responses won't expect an answer back, others do in some situations
     * @return bool
     */
    public function shouldExpectAnswer(): bool
    {
        return false;
    }
}
