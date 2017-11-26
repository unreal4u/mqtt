<?php

declare(strict_types=1);

namespace tests\unreal4u\MQTT\Mocks;

use unreal4u\MQTT\Internals\CommonFunctionality;
use unreal4u\MQTT\Internals\WritableContentInterface;
use unreal4u\MQTT\Internals\ReadableContentInterface;
use unreal4u\MQTT\Protocol\Connack;

class WritableBaseMock implements WritableContentInterface
{
    use CommonFunctionality;

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

    /**
     * Creates the fixed header each method has
     *
     * @param int $variableHeaderLength
     * @return string
     */
    public function createFixedHeader(int $variableHeaderLength): string
    {
        // TODO: Implement createFixedHeader() method.
    }

    /**
     * Creates the message to be sent
     * @return string
     */
    public function createSendableMessage(): string
    {
        // TODO: Implement createSendableMessage() method.
    }
}
