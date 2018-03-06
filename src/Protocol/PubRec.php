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
 * A PUBREC Packet is the response to a PUBLISH Packet with QoS 2.
 *
 * It is the second packet of the QoS 2 protocol exchange.
 */
final class PubRec extends ProtocolBase implements ReadableContentInterface, WritableContentInterface
{
    use ReadableContent, WritableContent, PacketIdentifierFunctionality;

    const CONTROL_PACKET_VALUE = 5;

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
     * Some responses won't expect an answer back, others do in some situations
     * @return bool
     */
    public function shouldExpectAnswer(): bool
    {
        return true;
    }

    /**
     * Any class can overwrite the default behaviour
     * @param ClientInterface $client
     * @param WritableContentInterface $originalRequest
     * @return bool
     */
    public function performSpecialActions(ClientInterface $client, WritableContentInterface $originalRequest): bool
    {
        $pubRel = new PubRel($this->logger);
        $pubRel->setPacketIdentifier($this->packetIdentifier);
        $pubComp = $client->processObject($pubRel);
        $this->logger->debug('Created PubRel as response, got PubComp back', ['PubComp' => $pubComp]);
        return true;
    }

    /**
     * @inheritdoc
     */
    public function getOriginControlPacket(): int
    {
        return Publish::getControlPacketValue();
    }
}
