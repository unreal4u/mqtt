<?php

declare(strict_types=1);

namespace unreal4u\MQTT\Protocol;

use unreal4u\MQTT\Internals\ClientInterface;
use unreal4u\MQTT\Internals\EventManager;
use unreal4u\MQTT\Internals\ProtocolBase;
use unreal4u\MQTT\Internals\ReadableContent;
use unreal4u\MQTT\Internals\ReadableContentInterface;
use unreal4u\MQTT\Internals\WritableContent;
use unreal4u\MQTT\Internals\WritableContentInterface;
use unreal4u\MQTT\Utilities;

final class PubRec extends ProtocolBase implements ReadableContentInterface, WritableContentInterface
{
    use ReadableContent, WritableContent;

    public $packetIdentifier = 0;

    const CONTROL_PACKET_VALUE = 5;

    public function fillObject(string $rawMQTTHeaders, ClientInterface $client): ReadableContentInterface
    {
        $this->packetIdentifier = $this->extractPacketIdentifier($rawMQTTHeaders);
        return $this;
    }

    /**
     * Creates the variable header that each method has
     * @return string
     * @throws \OutOfRangeException
     */
    public function createVariableHeader(): string
    {
        return Utilities::convertNumberToBinaryString($this->packetIdentifier);
    }

    /**
     * Creates the actual payload to be sent
     * @return string
     */
    public function createPayload(): string
    {
        return '';
    }

    /**
     * Some responses won't expect an answer back, others do in some situations
     * @return bool
     */
    public function shouldExpectAnswer(): bool
    {
        return true;
    }

    /**
     * Any class can overwrite the default behaviour
     * @param ClientInterface $client
     * @param WritableContentInterface $originalRequest
     * @return bool
     */
    public function performSpecialActions(ClientInterface $client, WritableContentInterface $originalRequest): bool
    {
        $pubRel = new PubRel($this->logger);
        $pubRel->packetIdentifier = $this->packetIdentifier;
        $pubComp = $client->sendData($pubRel);
        $this->logger->debug('Created PubRel as response, got PubComp back', ['PubComp' => $pubComp]);
        return true;
    }

    /**
     * Will return an object of the type the broker has returned to us
     *
     * @param string $data
     * @param ClientInterface $client
     *
     * @return ReadableContentInterface
     * @throws \DomainException
     */
    public function expectAnswer(string $data, ClientInterface $client): ReadableContentInterface
    {
        $this->logger->info('String of incoming data confirmed, returning new object', ['callee' => \get_class($this)]);

        $eventManager = new EventManager($this->logger);
        $object = $eventManager->analyzeHeaders($data, $client);
        if ($object instanceof PubRel) {

        }

        return $object;
    }

}
