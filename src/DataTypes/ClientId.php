<?php

declare(strict_types=1);

namespace unreal4u\MQTT\DataTypes;

/**
 * This Value Object will always contain a valid Packet Identifier
 */
final class ClientId
{
    /**
     * This field indicates the name of the clientId that we'll pass on to the broker.
     *
     * @var string
     */
    private $clientId;

    /**
     * QoSLevel constructor.
     *
     * @param int $packetIdentifier
     * @throws \OutOfRangeException
     */
    public function __construct(string $clientId)
    {
        $this->clientId = $clientId;
    }

    /**
     * Gets the current clientId
     *
     * @return string
     */
    public function getClientId(): string
    {
        return $this->clientId;
    }

    public function __toString(): string
    {
        return $this->getClientId();
    }

    public function isEmptyClientId(): bool
    {
        return $this->clientId === '';
    }

    public function performStrictValidationCheck(): string
    {
        $possibleProblem = '';

        $utf8ClientIdSize = \mb_strlen($this->clientId);

        if ($this->isEmptyClientId()) {
            /*
             * If you ever wind up in this situation, search for MQTT-3.1.3-7 on the following document for more
             * information: http://docs.oasis-open.org/mqtt/mqtt/v3.1.1/os/mqtt-v3.1.1-os.html#_Toc398718067
             */
            $possibleProblem = 'ClientId size is 0 bytes. This has several implications, check comments';
        } elseif ($utf8ClientIdSize > 23) {
            $possibleProblem = 'The broker MAY reject the connection because the ClientId is too long';
        } elseif (\strlen($this->clientId) !== $utf8ClientIdSize) {
            $possibleProblem = 'The broker MAY reject the connection because of invalid characters';
        }

        return $possibleProblem;
    }
}
