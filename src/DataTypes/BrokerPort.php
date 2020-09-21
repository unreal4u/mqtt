<?php

declare(strict_types=1);

namespace unreal4u\MQTT\DataTypes;

use unreal4u\MQTT\Exceptions\InvalidBrokerPort;
use unreal4u\MQTT\Exceptions\InvalidBrokerProtocol;

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
     * If we should connect through SSL or TLS, this parameter should be set to true
     * @var string
     */
    private $transmissionProtocol;

    /**
     * List of valid protocols that this package implements (or the broker can implement)
     * @var string[]
     */
    private static $validProtocols = [
        'tcp',
        'ssl',
        'tlsv1.0',
        'tlsv1.1',
        'tlsv1.2',
    ];

    /**
     * BrokerPort constructor.
     *
     * @param int $brokerPort
     * @param string $transmissionProtocol
     * @throws \unreal4u\MQTT\Exceptions\InvalidBrokerProtocol
     * @throws \unreal4u\MQTT\Exceptions\InvalidBrokerPort
     */
    public function __construct(int $brokerPort = 1883, string $transmissionProtocol = 'tcp')
    {
        if ($brokerPort > 65535 || $brokerPort < 1) {
            throw new InvalidBrokerPort(sprintf(
                'The provided broker port is invalid. Valid values are between 1 and 65535 (Provided: %d)',
                $brokerPort
            ));
        }

        if (\in_array($transmissionProtocol, self::$validProtocols, true) === false) {
            throw new InvalidBrokerProtocol(sprintf(
                'You must provide a valid protocol (Provided: %s)',
                $transmissionProtocol
            ));
        }

        $this->brokerPort = $brokerPort;
        $this->transmissionProtocol = $transmissionProtocol;
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

    /**
     * @return string
     */
    public function getTransmissionProtocol(): string
    {
        return $this->transmissionProtocol;
    }
}
