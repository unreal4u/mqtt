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
 * A PUBREL Packet is the response to a PUBREC Packet.
 *
 * It is the third packet of the QoS 2 protocol exchange.
 */
final class PubRel extends ProtocolBase implements ReadableContentInterface, WritableContentInterface
{
    use ReadableContent, WritableContent, PacketIdentifierFunctionality;

    const CONTROL_PACKET_VALUE = 6;

    /**
     * @param string $rawMQTTHeaders
     * @param ClientInterface $client
     * @return ReadableContentInterface
     * @throws \OutOfRangeException
     */
    public function fillObject(string $rawMQTTHeaders, ClientInterface $client): ReadableContentInterface
    {
        $rawHeadersSize = \strlen($rawMQTTHeaders);
        // A PubRel message is always 4 bytes in size
        if ($rawHeadersSize !== 4) {
            $this->logger->debug('Headers are smaller than 4 bytes, retrieving the rest', [
                'currentSize' => $rawHeadersSize
            ]);
            $rawMQTTHeaders .= $client->readBrokerData(4 - $rawHeadersSize);
        }
        $this->setPacketIdentifierFromRawHeaders($rawMQTTHeaders);
        $this->logger->debug('Determined packet identifier', ['PI' => $this->packetIdentifier]);

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
        if ($originalRequest instanceof PubRec) {
            $this->logger->debug('Checking packet identifier on PubRel', [
                'pubRelPI' => $this->getPacketIdentifier(),
                'originalRequestPI' => $originalRequest->getPacketIdentifier(),
            ]);

            if ($this->getPacketIdentifier() !== $originalRequest->getPacketIdentifier()) {
                throw new \LogicException('Packet identifiers to not match!');
            }

            $pubComp = new PubComp($this->logger);
            $pubComp->setPacketIdentifier($this->packetIdentifier);
            $client->processObject($pubComp);

            return true;
        }

        $this->logger->warning('Original request NOT a PubRec, ignoring object entirely');
        return false;
    }

    /**
     * @inheritdoc
     */
    public function getOriginControlPacket(): int
    {
        return PubRec::getControlPacketValue();
    }
}
