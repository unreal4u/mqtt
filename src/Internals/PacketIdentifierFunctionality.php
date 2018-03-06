<?php

declare(strict_types=1);

namespace unreal4u\MQTT\Internals;

use unreal4u\MQTT\DataTypes\PacketIdentifier;
use unreal4u\MQTT\Utilities;

/**
 * Trait ReadableContent
 * @package unreal4u\MQTT\Internals
 */
trait PacketIdentifierFunctionality
{
    /**
     * The packet identifier variable
     * @var PacketIdentifier
     */
    private $packetIdentifier;

    final public function setPacketIdentifier(PacketIdentifier $packetIdentifier): self
    {
        $this->packetIdentifier = $packetIdentifier;
        return $this;
    }

    final public function getPacketIdentifier(): int
    {
        return $this->packetIdentifier->getPacketIdentifierValue();
    }

    /**
     * Returns the binary representation of the packet identifier
     *
     * @return string
     * @throws \OutOfRangeException
     */
    final public function getPacketIdentifierBinaryRepresentation(): string
    {
        if ($this->packetIdentifier === null) {
            $this->generateRandomPacketIdentifier();
        }

        return Utilities::convertNumberToBinaryString($this->packetIdentifier->getPacketIdentifierValue());
    }

    /**
     * Sets the packet identifier straight from the raw MQTT headers
     *
     * @param string $rawMQTTHeaders
     * @return self
     * @throws \OutOfRangeException
     */
    final public function setPacketIdentifierFromRawHeaders(string $rawMQTTHeaders): self
    {
        $this->packetIdentifier = new PacketIdentifier(
            Utilities::convertBinaryStringToNumber($rawMQTTHeaders{2} . $rawMQTTHeaders{3})
        );

        return $this;
    }

    final public function generateRandomPacketIdentifier(): self
    {
        try {
            $this->packetIdentifier = new PacketIdentifier(random_int(1, 65535));
        } catch (\Exception $e) {
            /*
             * Default to an older method, there should be no security issues here I believe.
             *
             * If I am mistaken, please contact me at https://t.me/unreal4u
             */
            /** @noinspection ExceptionsAnnotatingAndHandlingInspection */
            /** @noinspection RandomApiMigrationInspection */
            $this->packetIdentifier = new PacketIdentifier(mt_rand(1, 65535));
        }
        return $this;
    }
}
