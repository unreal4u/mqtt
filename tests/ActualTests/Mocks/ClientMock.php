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
    public function shutdownConnection(): bool
    {
        return false;
    }

    /**
     * @inheritdoc
     */
    public function readBrokerData(int $bytes): string
    {
        return '';
    }

    /**
     * @inheritdoc
     */
    public function readBrokerHeader(): string
    {
        return '';
    }

    /**
     * @inheritdoc
     */
    public function sendBrokerData(WritableContentInterface $object): string
    {
        return '';
    }

    /**
     * @inheritdoc
     */
    public function enableSynchronousTransfer(bool $newStatus): ClientInterface
    {
        return $this;
    }

    /**
     * @inheritdoc
     */
    public function processObject(WritableContentInterface $object): ReadableContentInterface
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
