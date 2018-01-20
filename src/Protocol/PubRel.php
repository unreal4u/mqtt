<?php

declare(strict_types=1);

namespace unreal4u\MQTT\Protocol;

use unreal4u\MQTT\Internals\ClientInterface;
use unreal4u\MQTT\Internals\ProtocolBase;
use unreal4u\MQTT\Internals\ReadableContent;
use unreal4u\MQTT\Internals\ReadableContentInterface;
use unreal4u\MQTT\Internals\WritableContent;
use unreal4u\MQTT\Internals\WritableContentInterface;

final class PubRel extends ProtocolBase implements ReadableContentInterface, WritableContentInterface
{
    use ReadableContent, WritableContent;

    public $packetIdentifier = 0;

    const CONTROL_PACKET_VALUE = 6;

    public function fillObject(string $rawMQTTHeaders, ClientInterface $client): ReadableContentInterface
    {
        $this->packetIdentifier = $this->extractPacketIdentifier($rawMQTTHeaders);
        return $this;
    }

    /**
     * Creates the variable header that each method has
     * @return string
     */
    public function createVariableHeader(): string
    {
        // TODO: Implement createVariableHeader() method.
        return '';
    }

    /**
     * Creates the actual payload to be sent
     * @return string
     */
    public function createPayload(): string
    {
        // TODO: Implement createPayload() method.
        return '';
    }

    /**
     * @inheritdoc
     */
    public function expectAnswer(string $data, ClientInterface $client): ReadableContentInterface
    {
        return $this;
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
