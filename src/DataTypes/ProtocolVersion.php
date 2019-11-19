<?php

declare(strict_types=1);

namespace unreal4u\MQTT\DataTypes;

use unreal4u\MQTT\Exceptions\Connect\UnacceptableProtocolVersion;

use function chr;
use function in_array;

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

    private const SUPPORTED_PROTOCOL_VERSIONS = [
        '3.1.1'
    ];

    /**
     * QoSLevel constructor.
     *
     * @param string $protocolVersion
     * @throws UnacceptableProtocolVersion
     */
    public function __construct(string $protocolVersion)
    {
        if (in_array($protocolVersion, self::SUPPORTED_PROTOCOL_VERSIONS, true) === false) {
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

    /**
     * Will return the correct connection identifier for the current protocol
     *
     * @return string
     */
    public function getProtocolVersionBinaryRepresentation(): string
    {
        if ($this->protocolVersion === '3.1.1') {
            // Protocol v3.1.1 must return a 4
            return chr(4);
        }

        // Return a default of 0, which will be invalid anyway (but data will be sent to the broker this way)
        return chr(0);
    }

    public function __toString(): string
    {
        return $this->getProtocolVersion();
    }
}
