<?php

declare(strict_types=1);

namespace unreal4u\MQTT\DataTypes;

/**
 * This Value Object will set a clientId and be able to diagnose problems with the clientId's value
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
     * @param string $clientId
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

    /**
     * @return \Generator|string[]
     */
    public function performStrictValidationCheck(): \Generator
    {
        $utf8ClientIdSize = \mb_strlen($this->clientId);

        if ($this->isEmptyClientId()) {
            /*
             * If you ever wind up in this situation, search for MQTT-3.1.3-7 on the following document for more
             * information: http://docs.oasis-open.org/mqtt/mqtt/v3.1.1/os/mqtt-v3.1.1-os.html#_Toc398718067
             */
            yield 'ClientId size is 0 bytes. This has several implications, check comments';
        }

        if ($utf8ClientIdSize > 23) {
            yield 'The broker MAY reject the connection because the ClientId is too long';
        }

        if (\strlen($this->clientId) !== $utf8ClientIdSize) {
            yield 'The broker MAY reject the connection because of invalid characters';
        }
    }
}
