<?php

declare(strict_types=1);

namespace unreal4u\MQTT\Protocol;

use unreal4u\MQTT\Application\EmptyReadableResponse;
use unreal4u\MQTT\Application\Topic;
use unreal4u\MQTT\Internals\ClientInterface;
use unreal4u\MQTT\Internals\EventManager;
use unreal4u\MQTT\Internals\ProtocolBase;
use unreal4u\MQTT\Internals\ReadableContentInterface;
use unreal4u\MQTT\Internals\WritableContent;
use unreal4u\MQTT\Internals\WritableContentInterface;
use unreal4u\MQTT\Utilities;

final class Subscribe extends ProtocolBase implements WritableContentInterface
{
    use WritableContent;

    const CONTROL_PACKET_VALUE = 8;

    private $packetIdentifier = 0;

    /**
     * An array of topics on which to subscribe to
     * @var Topic[]
     */
    private $topics = [];

    /**
     * @return string
     * @throws \OutOfRangeException
     * @throws \Exception
     */
    public function createVariableHeader(): string
    {
        // Subscribe must always send a 2 flag
        $this->specialFlags = 2;

        // Assign a packet identifier automatically if none has been assigned yet
        if ($this->packetIdentifier === 0) {
            $this->setPacketIdentifier(random_int(0, 65535));
        }

        return Utilities::convertNumberToBinaryString($this->packetIdentifier);
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
     * When the Server receives a SUBSCRIBE Packet from a Client, the Server MUST respond with a SUBACK Packet
     *
     * This can however not be in the same order, as we may be able to receive PUBLISH packets before getting a SUBACK
     * back
     *
     * @see http://docs.oasis-open.org/mqtt/mqtt/v3.1.1/os/mqtt-v3.1.1-os.html#_Toc398718134 (MQTT-3.8.4-1)
     * @return bool
     */
    public function shouldExpectAnswer(): bool
    {
        return true;
    }

    /**
     * SUBSCRIBE Control Packets MUST contain a non-zero 16-bit Packet Identifier
     *
     * @param int $packetIdentifier
     * @return Subscribe
     * @throws \OutOfRangeException
     */
    public function setPacketIdentifier(int $packetIdentifier): Subscribe
    {
        if ($packetIdentifier > 65535 || $packetIdentifier < 1) {
            throw new \OutOfRangeException('Packet identifier must fit within 2 bytes');
        }

        $this->packetIdentifier = $packetIdentifier;
        $this->logger->debug('Setting packet identifier', ['current' => $this->packetIdentifier]);

        return $this;
    }

    public function getPacketIdentifier(): int
    {
        return $this->packetIdentifier;
    }

    /**
     * Performs a check on the socket connection and returns either the contents or an empty object
     *
     * @param ClientInterface $client
     * @return ReadableContentInterface
     * @throws \DomainException
     * @throws \unreal4u\MQTT\Exceptions\NotConnected
     * @throws \unreal4u\MQTT\Exceptions\Connect\NoConnectionParametersDefined
     */
    public function checkForEvent(ClientInterface $client): ReadableContentInterface
    {
        $this->checkPingTime($client);
        $publishPacketControlField = $client->readSocketData(1);
        $eventManager = new EventManager($this->logger);

        if ((\ord($publishPacketControlField) & 255) > 0) {
            $this->logger->debug('Event received', ['ordValue' => \ord($publishPacketControlField) & 255]);
            return $eventManager->analyzeHeaders($publishPacketControlField, $client);
        }

        $this->logger->debug('No valid publish packet control field found, returning empty response');
        return new EmptyReadableResponse($this->logger);
    }

    /**
     * Loop and yields different type of results back whenever they are available
     *
     * @param ClientInterface $client
     * @param int $idleMicroseconds The amount of microseconds the watcher should wait before checking the socket again
     * @return \Generator
     * @throws \DomainException
     * @throws \unreal4u\MQTT\Exceptions\NotConnected
     * @throws \unreal4u\MQTT\Exceptions\Connect\NoConnectionParametersDefined
     */
    public function loop(ClientInterface $client, int $idleMicroseconds = 100000): \Generator
    {
        // First of all: subscribe
        $this->logger->debug('Beginning loop', ['idleMicroseconds' => $idleMicroseconds]);
        $client->setBlocking(true);
        $readableContent = $client->sendData($this);
        // Set blocking explicitly to false due to messages not always arriving in the correct order
        $client->setBlocking(false);

        $isSubscribed = true;
        while ($isSubscribed) {
            $this->logger->debug('++Loop++');
            if ($readableContent instanceof Publish) {
                // Only if we receive a Publish event from the broker, yield the contents
                yield $readableContent->getMessage();
            } elseif ($readableContent instanceof UnSubAck) {
                // We have received an UnSubAck, which means we have to break the loop
                // TODO Improve this
                $isSubscribed = false;
            } else {
                // Only wait for a certain amount of time if there was nothing in the queue
                $this->logger->debug('Got an incoming object, disregarding', ['class' => \get_class($readableContent)]);
                usleep($idleMicroseconds);
            }

            $readableContent = $this->checkForEvent($client);
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
        $this->logger->debug('Topics added', ['totalTopics', count($this->topics)]);

        return $this;
    }

    /**
     * @param ClientInterface $client
     * @return bool
     */
    protected function checkPingTime(ClientInterface $client): bool
    {
        if ($client->isItPingTime()) {
            $this->logger->info('Pinging is needed, sending PingReq');
            $client->sendData(new PingReq($this->logger));
        }

        return true;
    }
}
