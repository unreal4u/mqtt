<?php

declare(strict_types=1);

namespace unreal4u\MQTT\Application;

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

    public function __construct(string $messageContents = null)
    {
        if ($messageContents !== null) {
            $this->setPayload($messageContents);
        }
    }

    public function setPayload(string $contents): PayloadInterface
    {
        $this->payload = $contents;
        return $this;
    }

    public function getPayload(): string
    {
        return $this->payload;
    }

    public function processIncomingPayload(string $contents): PayloadInterface
    {
        return $this;
    }

    public function getProcessedPayload(): string
    {
        return $this->payload;
    }
}
