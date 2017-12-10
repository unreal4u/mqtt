<?php

declare(strict_types=1);

namespace unreal4u\MQTT\Protocol;

use unreal4u\MQTT\Internals\ClientInterface;
use unreal4u\MQTT\Internals\ProtocolBase;
use unreal4u\MQTT\Internals\ReadableContent;
use unreal4u\MQTT\Internals\ReadableContentInterface;
use unreal4u\MQTT\Internals\WritableContent;
use unreal4u\MQTT\Internals\WritableContentInterface;
use unreal4u\MQTT\Utilities;

/**
 * Class PubAck
 * @package unreal4u\MQTT\Protocol
 */
final class PubAck extends ProtocolBase implements ReadableContentInterface, WritableContentInterface
{
    use ReadableContent;
    use WritableContent;

    public $packetIdentifier = 0;

    const CONTROL_PACKET_VALUE = 4;

    public function fillObject(string $rawMQTTHeaders): ReadableContentInterface
    {
        $this->packetIdentifier = $this->extractPacketIdentifier($rawMQTTHeaders);
        return $this;
    }

    /**
     * @inheritdoc
     * @throws \LogicException
     */
    public function performSpecialActions(ClientInterface $client, WritableContentInterface $originalRequest): bool
    {
        /** @var Publish $originalRequest */
        if ($this->packetIdentifier !== $originalRequest->packetIdentifier) {
            throw new \LogicException('Packet identifiers to not match!');
        }
        return true;
    }

    public function createVariableHeader(): string
    {
        return Utilities::convertNumberToBinaryString($this->packetIdentifier);
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
