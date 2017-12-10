<?php

declare(strict_types=1);

namespace tests\unreal4u\MQTT\Mocks;

use Psr\Log\LoggerInterface;
use unreal4u\MQTT\Internals\ClientInterface;
use unreal4u\MQTT\Internals\ReadableContentInterface;
use unreal4u\MQTT\Internals\WritableContentInterface;
use unreal4u\MQTT\Protocol\ConnAck;

class ClientMock implements ClientInterface
{
    private $socket;

    /**
     * @inheritdoc
     */
    public function __construct(LoggerInterface $logger = null)
    {
    }

    /**
     * @inheritdoc
     */
    public function __destruct()
    {
    }

    /**
     * @inheritdoc
     */
    public function getSocket()
    {
        return $this->socket;
    }

    /**
     * @inheritdoc
     */
    public function readSocketData(int $bytes): string
    {
        return '';
    }

    /**
     * @inheritdoc
     */
    public function readSocketHeader(): string
    {
        return '';
    }

    /**
     * @inheritdoc
     */
    public function sendSocketData(WritableContentInterface $object): string
    {
        return '';
    }

    /**
     * @inheritdoc
     */
    public function setBlocking(bool $newStatus): ClientInterface
    {
        return $this;
    }

    /**
     * @inheritdoc
     */
    public function sendData(WritableContentInterface $object): ReadableContentInterface
    {
        return new ConnAck();
    }

    /**
     * @inheritdoc
     */
    public function isItPingTime(): bool
    {
        return false;
    }

    /**
     * @inheritdoc
     */
    public function updateLastCommunication(): ClientInterface
    {
        return $this;
    }

    /**
     * @inheritdoc
     */
    public function setConnected(bool $isConnected): ClientInterface
    {
        return $this;
    }

    /**
     * @inheritdoc
     */
    public function isConnected(): bool
    {
        return false;
    }
}
