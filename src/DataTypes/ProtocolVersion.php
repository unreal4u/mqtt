<?php

declare(strict_types=1);

namespace unreal4u\MQTT\DataTypes;

use unreal4u\MQTT\Exceptions\Connect\UnacceptableProtocolVersion;

/**
 * This Value Object will always contain a valid protocol version.
 */
final class ProtocolVersion
{
    /**
     * Holds the current protocol version
     *
     * @var string
     */
    private $protocolVersion;

    /**
     * QoSLevel constructor.
     *
     * @param string $protocolVersion
     * @throws \unreal4u\MQTT\Exceptions\Connect\UnacceptableProtocolVersion
     */
    public function __construct(string $protocolVersion = '3.1.1')
    {
        if ($protocolVersion !== '3.1.1') {
            throw new UnacceptableProtocolVersion('The specified protocol is invalid');
        }
        $this->protocolVersion = $protocolVersion;
    }

    /**
     * Gets the current protocol version
     *
     * @return string
     */
    public function getProtocolVersion(): string
    {
        return $this->protocolVersion;
    }

    public function __toString(): string
    {
        return $this->getProtocolVersion();
    }
}
