<?php

declare(strict_types=1);

namespace tests\unreal4u\MQTT\Mocks;

use unreal4u\MQTT\Internals\ClientInterface;
use unreal4u\MQTT\Internals\ProtocolBase;
use unreal4u\MQTT\Internals\WritableContentInterface;
use unreal4u\MQTT\Internals\ReadableContentInterface;
use unreal4u\MQTT\Protocol\ConnAck;

class WritableBaseMock extends ProtocolBase implements WritableContentInterface
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
     * @inheritdoc
     * @throws \unreal4u\MQTT\Exceptions\InvalidResponseType
     */
    public function expectAnswer(string $brokerBitStream, ClientInterface $client): ReadableContentInterface
    {
        $connAck = new ConnAck();
        $connAck->instantiateObject($brokerBitStream, $client);

        return $connAck;
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
        return '';
    }

    /**
     * Creates the message to be sent
     * @return string
     */
    public function createSendableMessage(): string
    {
        return '';
    }

    public static function getControlPacketValue(): int
    {
        return 0;
    }
}
