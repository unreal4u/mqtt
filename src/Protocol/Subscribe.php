<?php

declare(strict_types=1);

namespace unreal4u\MQTT\Protocol;

use unreal4u\MQTT\Application\EmptyReadableResponse;
use unreal4u\MQTT\Application\PayloadInterface;
use unreal4u\MQTT\Client;
use unreal4u\MQTT\Internals\ProtocolBase;
use unreal4u\MQTT\Internals\ReadableContentInterface;
use unreal4u\MQTT\Internals\WritableContent;
use unreal4u\MQTT\Internals\WritableContentInterface;
use unreal4u\MQTT\Protocol\Subscribe\Topic;

final class Subscribe extends ProtocolBase implements WritableContentInterface
{
    use WritableContent;

    const CONTROL_PACKET_VALUE = 8;

    public $packetIdentifier = 0;

    /**
     * An array of topics on which to subscribe to
     * @var Topic[]
     */
    private $topics = [];

    public function createVariableHeader(): string
    {
        // Subscribe must always send a 2 flag
        $this->specialFlags = 2;
        return \chr(0) . \chr($this->packetIdentifier);
    }

    public function createPayload(): string
    {

        $output = '';
        foreach ($this->topics as $topic) {
            // chr on QoS level is safe because it will create an 8-bit flag where the first 6 are only 0's
            $output .= $this->createUTF8String($topic->getTopicName()) . \chr($topic->getTopicQoSLevel());
        }
        return $output;
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

    /**
     * Performs a check on the socket connection and returns either the contents or an empty object
     *
     * @param Client $client
     * @param PayloadInterface $payloadType
     * @return ReadableContentInterface
     * @throws \unreal4u\MQTT\Exceptions\NotConnected
     * @throws \unreal4u\MQTT\Exceptions\Connect\NoConnectionParametersDefined
     * @throws \unreal4u\MQTT\Exceptions\ServerClosedConnection
     */
    public function checkForEvent(Client $client, PayloadInterface $payloadType): ReadableContentInterface
    {
        $this->updateCommunication($client);
        $publishPacketControlField = $client->readSocketData(1);
        if ((\ord($publishPacketControlField) & 0xf0) > 0) {
            $restOfBytes = $client->readSocketData(1);
            $payload = $client->readSocketData(\ord($restOfBytes));

            $publish = new Publish($this->logger);
            $publish->setPayloadType($payloadType);
            $publish->populate($publishPacketControlField . $restOfBytes . $payload);
            return $publish;
        }

        $this->logger->debug('No valid publish packet control field found, returning empty response');
        return new EmptyReadableResponse($this->logger);
    }

    /**
     * Performs a simple loop and yields results back whenever they are available
     *
     * @param Client $client
     * @param PayloadInterface $payloadObject
     * @param int $idleMicroseconds
     * @return \Generator
     * @throws \unreal4u\MQTT\Exceptions\NotConnected
     * @throws \unreal4u\MQTT\Exceptions\Connect\NoConnectionParametersDefined
     * @throws \unreal4u\MQTT\Exceptions\ServerClosedConnection
     */
    public function loop(Client $client, PayloadInterface $payloadObject, int $idleMicroseconds = 100000): \Generator
    {
        // First of all: subscribe
        $client->sendData($this);

        // After we are successfully subscribed, start to listen for events
        while (true) {
            $readableContent = $this->checkForEvent($client, $payloadObject);

            // Only if we receive a Publish event from the broker, yield the contents
            if ($readableContent instanceof Publish) {
                yield $readableContent->getMessage();
            } else {
                // Only wait for a certain amount of time if there was nothing in the queue
                usleep($idleMicroseconds);
            }
        }
    }

    /**
     * A subscription is based on filters, this function allows us to pass on filters
     *
     * @param Topic[] $topics
     * @return Subscribe
     */
    public function addTopics(Topic ...$topics): Subscribe
    {
        $this->topics = $topics;

        return $this;
    }

    /**
     * @param Client $client
     * @return bool
     * @throws \unreal4u\MQTT\Exceptions\NotConnected
     * @throws \unreal4u\MQTT\Exceptions\Connect\NoConnectionParametersDefined
     * @throws \unreal4u\MQTT\Exceptions\ServerClosedConnection
     */
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
