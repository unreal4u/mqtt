<?php

declare(strict_types=1);

namespace unreal4u\MQTT\Protocol;

use unreal4u\MQTT\Exceptions\Connect\IdentifierRejected;
use unreal4u\MQTT\Exceptions\Connect\NoConnectionParametersDefined;
use unreal4u\MQTT\Exceptions\MustProvideUsername;
use unreal4u\MQTT\Internals\ClientInterface;
use unreal4u\MQTT\Internals\EventManager;
use unreal4u\MQTT\Internals\ProtocolBase;
use unreal4u\MQTT\Internals\ReadableContentInterface;
use unreal4u\MQTT\Internals\WritableContent;
use unreal4u\MQTT\Internals\WritableContentInterface;
use unreal4u\MQTT\Protocol\Connect\Parameters;
use unreal4u\MQTT\Utilities;

/**
 * After a Network Connection is established by a Client to a Server, the first Packet sent from the Client to the
 * Server MUST be a CONNECT Packet
 */
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
    public function setConnectionParameters(Parameters $connectionParameters): self
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

    /**
     * @return string
     * @throws \OutOfRangeException
     */
    public function createVariableHeader(): string
    {
        $bitString = $this->createUTF8String('MQTT'); // Connect MUST begin with MQTT
        $bitString .= $this->connectionParameters->getProtocolVersionBinaryRepresentation(); // Protocol level
        $bitString .= \chr($this->connectionParameters->getFlags());
        $bitString .= Utilities::convertNumberToBinaryString($this->connectionParameters->keepAlivePeriod);
        return $bitString;
    }

    public function createPayload(): string
    {
        // The order in a connect string is clientId first
        $output = $this->createUTF8String((string)$this->connectionParameters->getClientId());

        // Then the willTopic if it is set
        $output .= $this->createUTF8String($this->connectionParameters->getWillTopic());

        // The willMessage will come next
        $output .= $this->createUTF8String($this->connectionParameters->getWillMessage());

        // If the username is set, it will come next
        $output .= $this->createUTF8String($this->connectionParameters->getUsername());

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

    /**
     * Special handling of the ConnAck object: be able to inject more information into the object before throwing it
     *
     * @param string $data
     * @param ClientInterface $client
     *
     * @return ReadableContentInterface
     * @throws \DomainException
     * @throws \unreal4u\MQTT\Exceptions\Connect\IdentifierRejected
     */
    public function expectAnswer(string $data, ClientInterface $client): ReadableContentInterface
    {
        $this->logger->info('String of incoming data confirmed, returning new object', ['callee' => \get_class($this)]);

        $eventManager = new EventManager($this->logger);
        try {
            $connAck = $eventManager->analyzeHeaders($data, $client);
        } catch (IdentifierRejected $e) {
            $possibleReasons = '';
            foreach ($this->connectionParameters->getClientId()->performStrictValidationCheck() as $errorMessage) {
                $possibleReasons .= $errorMessage . PHP_EOL;
            }

            $e->fillPossibleReason($possibleReasons);
            // Re-throw the exception with all information filled in
            throw $e;
        }

        return $connAck;
    }
}
