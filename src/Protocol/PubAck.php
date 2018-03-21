<?php

declare(strict_types=1);

namespace unreal4u\MQTT\Protocol;

use unreal4u\MQTT\Internals\ClientInterface;
use unreal4u\MQTT\Internals\PacketIdentifierFunctionality;
use unreal4u\MQTT\Internals\ProtocolBase;
use unreal4u\MQTT\Internals\ReadableContent;
use unreal4u\MQTT\Internals\ReadableContentInterface;
use unreal4u\MQTT\Internals\WritableContent;
use unreal4u\MQTT\Internals\WritableContentInterface;

/**
 * A PUBACK Packet is the response to a PUBLISH Packet with QoS level 1.
 */
final class PubAck extends ProtocolBase implements ReadableContentInterface, WritableContentInterface
{
    use ReadableContent, WritableContent, PacketIdentifierFunctionality;

    const CONTROL_PACKET_VALUE = 4;

    /**
     * @param string $rawMQTTHeaders
     * @param ClientInterface $client
     * @return ReadableContentInterface
     * @throws \OutOfRangeException
     */
    public function fillObject(string $rawMQTTHeaders, ClientInterface $client): ReadableContentInterface
    {
        $this->setPacketIdentifierFromRawHeaders($rawMQTTHeaders);
        return $this;
    }

    /**
     * @inheritdoc
     * @throws \LogicException
     */
    public function performSpecialActions(ClientInterface $client, WritableContentInterface $originalRequest): bool
    {
        return $this->controlPacketIdentifiers($originalRequest);
    }

    /**
     * @return string
     * @throws \OutOfRangeException
     */
    public function createVariableHeader(): string
    {
        return $this->getPacketIdentifierBinaryRepresentation();
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

    /**
     * @inheritdoc
     */
    public function getOriginControlPacket(): int
    {
        return Publish::getControlPacketValue();
    }
}
