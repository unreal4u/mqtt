<?php

declare(strict_types=1);

namespace unreal4u\MQTT\Protocol;

use unreal4u\MQTT\Application\EmptyReadableResponse;
use unreal4u\MQTT\Client;
use unreal4u\MQTT\Internals\CommonFunctionality;
use unreal4u\MQTT\Internals\ReadableContentInterface;
use unreal4u\MQTT\Internals\WritableContent;
use unreal4u\MQTT\Internals\WritableContentInterface;

final class Subscribe implements WritableContentInterface
{
    use CommonFunctionality;
    use WritableContent;

    const CONTROL_PACKET_VALUE = 8;

    public $packetIdentifier = 10;

    public $topic = '';

    public $qosLevel = 0;

    public function createVariableHeader(): string
    {
        // Subscribe must always send a 2 flag
        $this->specialFlags = 2;
        return \chr(0) . \chr($this->packetIdentifier);
    }

    public function createPayload(): string
    {
        if ($this->topic === '') {
            throw new \InvalidArgumentException('A topic name must be specified');
        }

        return $this->createUTF8String($this->topic) . \chr($this->qosLevel);
    }

    /**
     * QoS level 0 does not have to wait for a answer, so return false. Any other QoS level returns true
     * @return bool
     */
    public function shouldExpectAnswer(): bool
    {
        return true;
    }

    public function expectAnswer(string $data): ReadableContentInterface
    {
        $this->logger->info('String of incoming data confirmed, returning new object', ['class' => \get_class($this)]);
        $subAck = new SubAck($this->logger);
        $subAck->populate($data);

        return $subAck;
    }

    public function checkForEvent(Client $client): ReadableContentInterface
    {
        $this->updateCommunication($client);
        $publishPacketControlField = $client->readSocketData(1);
        if ((\ord($publishPacketControlField) & 0xf0) > 0) {
            $restOfBytes = $client->readSocketData(1);
            $payload = $client->readSocketData(\ord($restOfBytes));

            $publish = new Publish($this->logger);
            $publish->populate($publishPacketControlField . $restOfBytes . $payload);
            return $publish;
        }

        $this->logger->debug('No valid publish packet control field found, returning empty response');
        return new EmptyReadableResponse($this->logger);
    }

    private function updateCommunication(Client $client): bool
    {
        $this->logger->debug('Checking ping');
        if ($client->needsCommunication()) {
            $this->logger->notice('Sending ping');
            $client->setBlocking(true);
            $client->sendData(new PingReq($this->logger));
            $client->setBlocking(false);
        }

        return true;
    }
}
