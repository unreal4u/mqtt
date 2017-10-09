<?php

declare(strict_types=1);

namespace unreal4u\MQTT\Application;

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
