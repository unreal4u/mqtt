<?php

declare(strict_types=1);

namespace unreal4u\MQTT\DataTypes;

use unreal4u\MQTT\Exceptions\InvalidBrokerPort;

/**
 * This Value Object will always contain a valid broker port.
 */
final class BrokerPort
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
    private $brokerPort;

    /**
     * BrokerPort constructor.
     *
     * @param int $brokerPort
     * @throws \unreal4u\MQTT\Exceptions\InvalidBrokerPort
     */
    public function __construct(int $brokerPort = 1883)
    {
        if ($brokerPort > 65535 || $brokerPort < 1) {
            throw new InvalidBrokerPort(sprintf(
                'The provided broker port is invalid. Valid values are between 1 and 65535 (Provided: %d)',
                $brokerPort
            ));
        }

        $this->brokerPort = $brokerPort;
    }

    /**
     * Gets the current broker port
     *
     * @return int
     */
    public function getBrokerPort(): int
    {
        return $this->brokerPort;
    }
}
