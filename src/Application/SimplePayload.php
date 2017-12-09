<?php

declare(strict_types=1);

namespace unreal4u\MQTT\Application;

use Psr\Log\LoggerInterface;
use unreal4u\MQTT\DummyLogger;

/**
 * The most basic functionality: send the payload as-is provided, without any additional processing on the data
 */
final class SimplePayload implements PayloadInterface
{
    /**
     * The contents of the payload itself
     * @var string
     */
    private $payload = '';

    /**
     * @var LoggerInterface
     */
    private $logger;

    public function __construct(string $messageContents = null, LoggerInterface $logger = null)
    {
        // Set the logger before setting the payload so that we already have it in the object
        if ($logger === null) {
            $logger = new DummyLogger();
        }
        $this->logger = $logger;

        if ($messageContents !== null) {
            $this->setPayload($messageContents);
        }
    }

    public function setPayload(string $contents): PayloadInterface
    {
        $this->payload = $contents;
        $this->logger->debug('Setting contents of payload', ['contents' => $contents]);
        return $this;
    }

    public function getPayload(): string
    {
        return $this->payload;
    }

    public function processIncomingPayload(string $contents): PayloadInterface
    {
        return $this->setPayload($contents);
    }

    public function getProcessedPayload(): string
    {
        return $this->payload;
    }
}
