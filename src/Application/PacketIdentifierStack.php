<?php

declare(strict_types=1);

namespace unreal4u\MQTT\Application;

use unreal4u\MQTT\Internals\WritableContentInterface;

/**
 * QoS Level 1 and 2 messages may come in out of order. Stack them so that we can find the appropriate response
 */
final class PacketIdentifierStack
{
    /**
     * @var array
     */
    private $stack = [];

    /**
     * Adds a packet identifiable object to the stack
     *
     * @param WritableContentInterface $writableContent
     * @return $this
     */
    public function add(WritableContentInterface $writableContent): self
    {
        // TODO Replace with some way of retrieving the packet identifier
        $this->stack[$writableContent->hasActivePacketIdentifier()] = $writableContent;
        return $this;
    }

    /**
     * Searches for a specific packetIdentifier in the stack
     *
     * @param int $packetIdentifier
     * @return bool
     */
    public function search(int $packetIdentifier): bool
    {
        // TODO Search for it or throw an Exception

        $this->delete($packetIdentifier);
        return true;
    }

    /**
     * TODO If this is a oneliner, maybe merge with above? Check this out later on
     *
     * @param int $packetIdentifier
     * @return $this
     */
    private function delete(int $packetIdentifier): self
    {
        unset($this->stack[$packetIdentifier]);
        return $this;
    }
}
