<?php

declare(strict_types=1);

namespace unreal4u\MQTT\Protocol;

use unreal4u\MQTT\Exceptions\Connect\NoConnectionParametersDefined;
use unreal4u\MQTT\Exceptions\MustProvideUsername;
use unreal4u\MQTT\Internals\ProtocolBase;
use unreal4u\MQTT\Internals\WritableContent;
use unreal4u\MQTT\Internals\WritableContentInterface;
use unreal4u\MQTT\Protocol\Connect\Parameters;
use unreal4u\MQTT\Utilities;

final class Connect extends ProtocolBase implements WritableContentInterface
{
    use WritableContent;

    const CONTROL_PACKET_VALUE = 1;

    /**
     * @var Parameters
     */
    private $connectionParameters;

    /**
     * Saves the mandatory connection parameters onto this object
     * @param Parameters $connectionParameters
     *
     * @return Connect
     */
    public function setConnectionParameters(Parameters $connectionParameters): Connect
    {
        $this->connectionParameters = $connectionParameters;
        return $this;
    }

    /**
     * Get the connection parameters from the private object
     *
     * @return Parameters
     * @throws \unreal4u\MQTT\Exceptions\Connect\NoConnectionParametersDefined
     */
    public function getConnectionParameters(): Parameters
    {
        if ($this->connectionParameters === null) {
            throw new NoConnectionParametersDefined('You must pass on the connection parameters before connecting');
        }

        return $this->connectionParameters;
    }

    public function createVariableHeader(): string
    {
        $bitString = $this->createUTF8String('MQTT'); // Connect MUST begin with MQTT
        $bitString .= $this->getProtocolLevel(); // Protocol level
        $bitString .= \chr($this->connectionParameters->getFlags());
        $bitString .= Utilities::convertNumberToBinaryString($this->connectionParameters->keepAlivePeriod);
        return $bitString;
    }

    public function createPayload(): string
    {
        $output = '';
        // The order in a connect string is clientId first
        if ($this->connectionParameters->getClientId() !== '') {
            $output .= $this->createUTF8String($this->connectionParameters->getClientId());
        }

        // Then the willTopic if it is set
        if ($this->connectionParameters->getWillTopic() !== '') {
            $output .= $this->createUTF8String($this->connectionParameters->getWillTopic());
        }

        // The willMessage will come next
        if ($this->connectionParameters->getWillMessage() !== '') {
            $output .= $this->createUTF8String($this->connectionParameters->getWillMessage());
        }

        // If the username is set, it will come next
        if ($this->connectionParameters->getUsername() !== '') {
            $output .= $this->createUTF8String($this->connectionParameters->getUsername());
        }

        // And finally the password as last parameter
        if ($this->connectionParameters->getPassword() !== '') {
            if ($this->connectionParameters->getUsername() === '') {
                throw new MustProvideUsername('A password can not be set without a username! Please set username');
            }
            $output .= $this->createUTF8String($this->connectionParameters->getPassword());
        }

        return $output;
    }

    public function shouldExpectAnswer(): bool
    {
        return true;
    }
}
