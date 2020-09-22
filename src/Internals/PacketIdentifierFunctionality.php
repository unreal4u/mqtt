<?php

declare(strict_types=1);

namespace unreal4u\MQTT\Internals;

use Exception;
use OutOfRangeException;
use unreal4u\MQTT\DataTypes\PacketIdentifier;
use unreal4u\MQTT\Exceptions\NonMatchingPacketIdentifiers;
use unreal4u\MQTT\Utilities;

use function mt_rand;

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
        if ($this->packetIdentifier === null) {
            $this->generateRandomPacketIdentifier();
        }

        return $this->packetIdentifier->getPacketIdentifierValue();
    }

    /**
     * Returns the binary representation of the packet identifier
     *
     * @return string
     * @throws OutOfRangeException
     */
    final public function getPacketIdentifierBinaryRepresentation(): string
    {
        return Utilities::convertNumberToBinaryString($this->getPacketIdentifier());
    }

    /**
     * Sets the packet identifier straight from the raw MQTT headers
     *
     * @param string $rawMQTTHeaders
     * @return self
     * @throws OutOfRangeException
     */
    final public function setPacketIdentifierFromRawHeaders(string $rawMQTTHeaders): self
    {
        $this->packetIdentifier = new PacketIdentifier(
            Utilities::convertBinaryStringToNumber($rawMQTTHeaders[2] . $rawMQTTHeaders[3])
        );

        return $this;
    }

    final public function generateRandomPacketIdentifier(): self
    {
        try {
            $this->packetIdentifier = new PacketIdentifier(random_int(1, 65535));
        } catch (Exception $e) {
            /*
             * Default to an older method, there should be no security issues here I believe.
             *
             * If I am mistaken, please contact me at https://t.me/unreal4u
             */
            /** @noinspection RandomApiMigrationInspection */
            $this->packetIdentifier = new PacketIdentifier(mt_rand(1, 65535));
        }
        return $this;
    }

    /**
     * Checks whether the original request with the current stored packet identifier matches
     *
     * @param WritableContentInterface $originalRequest
     * @throws NonMatchingPacketIdentifiers
     * @return bool
     */
    private function controlPacketIdentifiers(WritableContentInterface $originalRequest): bool
    {
        /** @var PacketIdentifierFunctionality $originalRequest */
        if ($this->getPacketIdentifier() !== $originalRequest->getPacketIdentifier()) {
            $this->logger->critical('Non matching packet identifiers found, throwing exception', [
                'original' => $originalRequest->getPacketIdentifier(),
                'response' => $this->getPacketIdentifier(),
            ]);

            $e = new NonMatchingPacketIdentifiers(sprintf(
                'Packet identifiers do not match: %d (original) vs %d (response)',
                $originalRequest->getPacketIdentifier(),
                $this->getPacketIdentifier()
            ));
            $e->setOriginPacketIdentifier(new PacketIdentifier($originalRequest->getPacketIdentifier()));
            $e->setReturnedPacketIdentifier($this->packetIdentifier);
            throw $e;
        }

        return true;
    }
}
