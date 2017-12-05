<?php

declare(strict_types=1);

namespace unreal4u\MQTT\Protocol;

use unreal4u\MQTT\Client;
use unreal4u\MQTT\Internals\ProtocolBase;
use unreal4u\MQTT\Internals\ReadableContent;
use unreal4u\MQTT\Internals\ReadableContentInterface;
use unreal4u\MQTT\Internals\WritableContent;
use unreal4u\MQTT\Internals\WritableContentInterface;

final class PubAck extends ProtocolBase implements ReadableContentInterface, WritableContentInterface
{
    use ReadableContent;
    use WritableContent;

    public $packetIdentifier = 0;

    const CONTROL_PACKET_VALUE = 4;

    public function fillObject(): ReadableContentInterface
    {
        // Fastest conversion? Turn the bytes around instead of trying to arm a number and passing it along
        $this->packetIdentifier = \ord($this->rawMQTTHeaders{3} . $this->rawMQTTHeaders{2});
        return $this;
    }

    /**
     * @inheritdoc
     * @throws \LogicException
     */
    public function performSpecialActions(Client $client, WritableContentInterface $originalRequest): bool
    {
        /** @var Publish $originalRequest */
        if ($this->packetIdentifier !== $originalRequest->packetIdentifier) {
            throw new \LogicException('Packet identifiers to not match!');
        }
        return true;
    }

    /**
     * Creates the variable header that each method has
     * @return string
     */
    public function createVariableHeader(): string
    {
        // TODO: Implement createVariableHeader() method.
    }

    /**
     * Creates the actual payload to be sent
     * @return string
     */
    public function createPayload(): string
    {
        // TODO: Implement createPayload() method.
    }

    /**
     * What specific kind of post we should expect back from this request
     *
     * @param string $data
     * @return ReadableContentInterface
     */
    public function expectAnswer(string $data): ReadableContentInterface
    {
        return $this;
    }

    /**
     * Some responses won't expect an answer back, others do in some situations
     * @return bool
     */
    public function shouldExpectAnswer(): bool
    {
        return false;
    }
}
