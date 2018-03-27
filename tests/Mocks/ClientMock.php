<?php

declare(strict_types=1);

namespace tests\unreal4u\MQTT\Mocks;

use Psr\Log\LoggerInterface;
use unreal4u\MQTT\Application\EmptyReadableResponse;
use unreal4u\MQTT\Internals\ClientInterface;
use unreal4u\MQTT\Internals\ReadableContentInterface;
use unreal4u\MQTT\Internals\WritableContentInterface;

class ClientMock implements ClientInterface
{
    private $updateLastCommunicationWasCalled = false;
    private $setConnectedWasCalled = false;
    private $shutdownConnectionWasCalled = false;
    private $readBrokerDataWasCalled = false;
    private $isItPingTimeWasCalled = false;
    private $processObjectWasCalledWithObjectType = '';

    private $itIsPingTimeNow = false;

    /**
     * This will be set to whatever data must be supposedly returned
     * @var string
     */
    private $brokerData = '';

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
        $this->shutdownConnectionWasCalled = true;
        return false;
    }

    public function returnSpecificBrokerData(string $streamData): self
    {
        $this->brokerData = $streamData;
        return $this;
    }

    /**
     * @inheritdoc
     */
    public function readBrokerData(int $bytes): string
    {
        $this->readBrokerDataWasCalled = true;
        $returnedString = substr($this->brokerData, 0, $bytes);
        $this->brokerData = substr($this->brokerData, $bytes);
        return $returnedString;
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
        $this->processObjectWasCalledWithObjectType = \get_class($object);
        return new EmptyReadableResponse();
    }

    /**
     * Set to whatever value isItPingTime should return
     *
     * @param bool $status
     * @return ClientMock
     */
    public function setPingTime(bool $status): self
    {
        $this->itIsPingTimeNow = $status;
        return $this;
    }

    /**
     * @inheritdoc
     */
    public function isItPingTime(): bool
    {
        $this->isItPingTimeWasCalled = true;
        return $this->itIsPingTimeNow;
    }

    /**
     * @inheritdoc
     */
    public function updateLastCommunication(): ClientInterface
    {
        $this->updateLastCommunicationWasCalled = true;
        return $this;
    }

    /**
     * @inheritdoc
     */
    public function setConnected(bool $isConnected): ClientInterface
    {
        $this->setConnectedWasCalled = true;
        return $this;
    }

    /**
     * @inheritdoc
     */
    public function isConnected(): bool
    {
        return false;
    }

    public function setConnectedWasCalled(): bool
    {
        return $this->setConnectedWasCalled;
    }

    public function updateLastCommunicationWasCalled(): bool
    {
        return $this->updateLastCommunicationWasCalled;
    }

    public function shutdownConnectionWasCalled(): bool
    {
        return $this->shutdownConnectionWasCalled;
    }

    public function readBrokerDataWasCalled(): bool
    {
        return $this->readBrokerDataWasCalled;
    }

    public function isItPingTimeWasCalled(): bool
    {
        return $this->isItPingTimeWasCalled;
    }

    public function processObjectWasCalledWithObjectType(): string
    {
        return $this->processObjectWasCalledWithObjectType;
    }
}
