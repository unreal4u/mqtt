<?php

declare(strict_types=1);

namespace unreal4u\MQTT\Protocol;

use LogicException;
use OutOfRangeException;
use unreal4u\MQTT\Internals\ClientInterface;
use unreal4u\MQTT\Internals\PacketIdentifierFunctionality;
use unreal4u\MQTT\Internals\ProtocolBase;
use unreal4u\MQTT\Internals\ReadableContent;
use unreal4u\MQTT\Internals\ReadableContentInterface;
use unreal4u\MQTT\Internals\WritableContentInterface;

use function ord;
use function strlen;
use function substr;

/**
 * A SUBACK Packet is sent by the Server to the Client to confirm receipt and processing of a SUBSCRIBE Packet.
 */
final class SubAck extends ProtocolBase implements ReadableContentInterface
{
    use ReadableContent;
    use PacketIdentifierFunctionality;

    private const CONTROL_PACKET_VALUE = 9;

    /**
     * Information about each topic
     * @var string
     */
    private $payload = '';

    /**
     * @param string $rawMQTTHeaders
     * @param ClientInterface $client
     * @return ReadableContentInterface
     * @throws OutOfRangeException
     */
    public function fillObject(string $rawMQTTHeaders, ClientInterface $client): ReadableContentInterface
    {
        // Read out the remaining length bytes should only 1 byte have come in until now
        if (strlen($rawMQTTHeaders) === 1) {
            $rawMQTTHeaders .= $client->readBrokerData(1);
        }

        $remainingLength = ord($rawMQTTHeaders[1]);
        // Check if we have a complete message
        if ($remainingLength + 2 !== strlen($rawMQTTHeaders)) {
            $rawMQTTHeaders .= $client->readBrokerData($remainingLength + 2 - strlen($rawMQTTHeaders));
        }

        $this->setPacketIdentifierFromRawHeaders($rawMQTTHeaders);
        // TODO Check which QoS corresponds to each topic we are subscribed to
        $this->payload = substr($rawMQTTHeaders, 4);
        return $this;
    }

    /**
     * @inheritdoc
     * @throws LogicException
     */
    public function performSpecialActions(ClientInterface $client, WritableContentInterface $originalRequest): bool
    {
        $this->controlPacketIdentifiers($originalRequest);
        $client->updateLastCommunication();
        return true;
    }

    /**
     * @inheritdoc
     */
    public function getOriginControlPacket(): int
    {
        return Subscribe::getControlPacketValue();
    }
}
