<?php

declare(strict_types=1);

namespace unreal4u\MQTT\DataTypes;

use unreal4u\MQTT\Exceptions\InvalidQoSLevel;

/**
 * This Value Object will always contain a valid QoS level.
 */
final class QoSLevel
{
    /**
     * This field indicates the level of assurance for delivery of an Application Message. Can be 0, 1 or 2
     *
     * 0: At most once delivery (default)
     * 1: At least once delivery
     * 2: Exactly once delivery
     *
     * @var int
     */
    private $qosLevel = 0;

    /**
     * QoSLevel constructor.
     * @param int $QoSLevel
     * @throws \unreal4u\MQTT\Exceptions\InvalidQoSLevel
     */
    public function __construct(int $qosLevel = 0)
    {
        if ($qosLevel > 2 || $qosLevel < 0) {
            throw new InvalidQoSLevel(sprintf(
                'The provided QoS level is invalid. Valid values are 0, 1 and 2 (Provided: %d)',
                $qosLevel
            ));
        }
        
        $this->qosLevel = $qosLevel;
    }

    /**
     * Gets the current QoS level
     *
     * @return int
     */
    public function getQoSLevel(): int
    {
        return $this->qosLevel;
    }
}
