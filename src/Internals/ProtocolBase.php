<?php

declare(strict_types=1);

namespace unreal4u\MQTT\Internals;

use Psr\Log\LoggerInterface;
use unreal4u\Dummy\Logger;

abstract class ProtocolBase
{
    /**
     * The actual logger object
     * @var LoggerInterface
     */
    protected $logger;

    /**
     * Base constructor for all protocol stuff
     * @param LoggerInterface|null $logger
     */
    final public function __construct(?LoggerInterface $logger = null)
    {
        if ($logger === null) {
            $logger = new Logger();
        }

        // Insert name of class within the logger
        $this->logger = $logger->withName(str_replace('unreal4u\\MQTT\\', '', \get_class($this)));

        $this->initializeObject();
    }

    /**
     * Should any method have any abnormal default behaviour, we can overwrite this method
     */
    protected function initializeObject(): ProtocolBase
    {
        return $this;
    }

    /**
     * If the object in question uses Packet Identifier functionality, it should be added to the stack
     */
    final public function hasActivePacketIdentifier(): bool
    {
        return property_exists($this, 'packetIdentifier') && $this->packetIdentifier !== null;
    }
}
