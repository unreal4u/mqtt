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
 * A PUBREL Packet is the response to a PUBREC Packet.
 *
 * It is the third packet of the QoS 2 protocol exchange.
 */
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
     * @throws \OutOfRangeException
     */
    public function createVariableHeader(): string
    {
        $this->specialFlags |= 2;
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
     * PUBREL should ALWAYS expect an answer back (in the form of a PUBCOMP)
     * @return bool
     */
    public function shouldExpectAnswer(): bool
    {
        return true;
    }

    /**
     * @param ClientInterface $client
     * @param WritableContentInterface $originalRequest
     * @return bool
     * @throws \LogicException
     */
    public function performSpecialActions(ClientInterface $client, WritableContentInterface $originalRequest): bool
    {
        $this->logger->debug('Checking packet identifier on PubRel');
        /** @var PubRec $originalRequest */
        if ($this->packetIdentifier !== $originalRequest->packetIdentifier) {
            throw new \LogicException('Packet identifiers to not match!');
        }

        $pubComp = new PubComp($this->logger);
        $pubComp->packetIdentifier = $this->packetIdentifier;
        $client->sendData($pubComp);

        return true;
    }

    /**
     * @inheritdoc
     */
    public function originPacketIdentifier(): int
    {
        return PubRec::getControlPacketValue();
    }
}
