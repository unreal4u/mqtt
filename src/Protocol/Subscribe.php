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
     * Indicates whether to continue the loop or break it at any point, cleanly without disconnecting from the broker
     * @var bool
     */
    private $shouldLoop = true;

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
        foreach ($this->getTopics() as $topic) {
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
     * @param callable|null $hookBeforeLoop
     * @return \Generator
     * @throws \unreal4u\MQTT\Exceptions\NotConnected
     * @throws \unreal4u\MQTT\Exceptions\Connect\NoConnectionParametersDefined
     * @throws \DomainException
     */
    public function loop(
        ClientInterface $client,
        int $idleMicroseconds = 100000,
        callable $hookBeforeLoop = null
    ): \Generator {
        $this->shouldLoop = true;
        // First of all: subscribe
        $this->logger->debug('Beginning loop', ['idleMicroseconds' => $idleMicroseconds]);
        $readableContent = $client->sendData($this);

        // Allow the user to do certain stuff before looping, for example: an Unsubscribe
        if (\is_callable($hookBeforeLoop)) {
            $this->logger->notice('Callable detected, executing', ['userFunctionName' => $hookBeforeLoop]);
            $hookBeforeLoop($this->logger);
        }

        while ($this->shouldLoop === true) {
            $this->logger->debug('++Loop++');
            if ($readableContent instanceof Publish) {
                // Only if we receive a Publish event from the broker, yield the contents
                yield $readableContent->getMessage();
            } else {
                // Only wait for a certain amount of time if there was nothing in the queue
                $this->logger->debug('Got an incoming object, but not a message, disregarding', [
                    'class' => \get_class($readableContent),
                ]);
                usleep($idleMicroseconds);
            }

            $readableContent = $this->checkForEvent($client);
        }
    }

    /**
     * Call this function to break out of the loop cleanly
     *
     * There is no way to know on which topics we are still subscribed on. This function lets us exit the above loop
     * cleanly without the need to disconnect from the broker.
     *
     * @return Subscribe
     */
    public function breakLoop(): self
    {
        $this->shouldLoop = false;
        return $this;
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
        $this->logger->debug('Topics added', ['totalTopics', $this->getNumberOfTopics()]);

        return $this;
    }

    /**
     * Returns the current number of topics
     *
     * @return int
     */
    public function getNumberOfTopics(): int
    {
        return count($this->topics);
    }

    /**
     * Returns the topics in the order they were inserted / requested
     *
     * The order is important because SUBACK will return the status code for each topic in this order, without
     * explicitly identifying which topic is which
     *
     * @return \Generator|Topic[]
     */
    public function getTopics(): \Generator
    {
        foreach ($this->topics as $topic) {
            yield $topic;
        }
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
