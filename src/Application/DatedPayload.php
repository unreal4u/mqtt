<?php

declare(strict_types=1);

namespace unreal4u\MQTT\Application;

use Psr\Log\LoggerInterface;
use unreal4u\MQTT\DummyLogger;

/**
 * This is an example of a payload class that performs some processing on the data
 *
 * This particular case will prepend a datetime to the message itself. It will json_encode() it into the payload and
 * when retrieving it will set the public property $originalPublishDateTime.
 */
final class DatedPayload implements PayloadInterface
{
    /**
     * The actual payload contents
     * @var string
     */
    private $payload = '';

    /**
     * @var \DateTimeImmutable
     */
    public $originalPublishDateTime;

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
        return $this;
    }

    public function getPayload(): string
    {
        return $this->payload;
    }

    public function processIncomingPayload(string $contents): PayloadInterface
    {
        $this->logger->debug('Processing incoming payload');
        $decodedData = json_decode($contents, true);
        $this->originalPublishDateTime = new \DateTimeImmutable($decodedData['publishDateTime']);
        $this->payload = $decodedData['payload'];
        return $this;
    }

    public function getProcessedPayload(): string
    {
        return json_encode(['publishDateTime' => date('r'), 'payload' => $this->payload]);
    }
}
