<?php

declare(strict_types=1);

namespace unreal4u\MQTT\Protocol;

use unreal4u\MQTT\Internals\ClientInterface;
use unreal4u\MQTT\Internals\ProtocolBase;
use unreal4u\MQTT\Internals\ReadableContent;
use unreal4u\MQTT\Internals\ReadableContentInterface;
use unreal4u\MQTT\Internals\WritableContentInterface;

/**
 * Class SubAck
 * @package unreal4u\MQTT\Protocol
 */
final class SubAck extends ProtocolBase implements ReadableContentInterface
{
    use ReadableContent;

    const CONTROL_PACKET_VALUE = 9;

    /**
     * @var int
     */
    private $packetIdentifier = 0;

    public function fillObject(string $rawMQTTHeaders, ClientInterface $client): ReadableContentInterface
    {
        // Read the rest of the request out should only 1 byte have come in
        if (mb_strlen($rawMQTTHeaders) === 1) {
            $rawMQTTHeaders .= $client->readSocketData(3);
        }

        $this->packetIdentifier = $this->extractPacketIdentifier($rawMQTTHeaders);
        return $this;
    }

    /**
     * @inheritdoc
     * @throws \LogicException
     */
    public function performSpecialActions(ClientInterface $client, WritableContentInterface $originalRequest): bool
    {
        /** @var Subscribe $originalRequest */
        if ($this->packetIdentifier !== $originalRequest->getPacketIdentifier()) {
            throw new \LogicException('Packet identifiers do not match!');
        }

        $client
            ->updateLastCommunication()
            ->setBlocking(false)
        ;
        return true;
    }
}
