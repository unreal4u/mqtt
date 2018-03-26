<?php

declare(strict_types=1);

namespace unreal4u\MQTT\Protocol;

use unreal4u\MQTT\Application\EmptyReadableResponse;
use unreal4u\MQTT\Internals\ClientInterface;
use unreal4u\MQTT\Internals\PacketIdentifierFunctionality;
use unreal4u\MQTT\Internals\ProtocolBase;
use unreal4u\MQTT\Internals\ReadableContent;
use unreal4u\MQTT\Internals\ReadableContentInterface;
use unreal4u\MQTT\Internals\WritableContent;
use unreal4u\MQTT\Internals\WritableContentInterface;

/**
 * The PUBCOMP Packet is the response to a PUBREL Packet.
 *
 * It is the fourth and final packet of the QoS 2 protocol exchange.
 *
 * QoS lvl2:
 *   First packet: PUBLISH
 *   Second packet: PUBREC
 *   Third packet: PUBREL
 *   Fourth packet: PUBCOMP
 *
 * @see https://go.gliffy.com/go/publish/12498076
 */
final class PubComp extends ProtocolBase implements ReadableContentInterface, WritableContentInterface
{
    use ReadableContent, WritableContent, PacketIdentifierFunctionality;

    const CONTROL_PACKET_VALUE = 7;

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
     * Creates the variable header that each method has
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
        return new EmptyReadableResponse($this->logger);
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
        return PubRel::getControlPacketValue();
    }
}
