<?php

declare(strict_types=1);

namespace unreal4u\MQTT\Protocol;

use DomainException;
use Exception;
use Generator;
use OutOfRangeException;
use SplQueue;
use unreal4u\MQTT\Application\EmptyReadableResponse;
use unreal4u\MQTT\Exceptions\Connect\NoConnectionParametersDefined;
use unreal4u\MQTT\Exceptions\MustContainTopic;
use unreal4u\MQTT\Exceptions\NotConnected;
use unreal4u\MQTT\Internals\ClientInterface;
use unreal4u\MQTT\Internals\EventManager;
use unreal4u\MQTT\Internals\PacketIdentifierFunctionality;
use unreal4u\MQTT\Internals\ProtocolBase;
use unreal4u\MQTT\Internals\ReadableContentInterface;
use unreal4u\MQTT\Internals\TopicFilterFunctionality;
use unreal4u\MQTT\Internals\WritableContent;
use unreal4u\MQTT\Internals\WritableContentInterface;

use function chr;
use function is_callable;
use function ord;
use function strlen;
use function usleep;

/**
 * The SUBSCRIBE Packet is sent from the Client to the Server to create one or more Subscriptions.
 *
 * Each Subscription registers a Client’s interest in one or more Topics. The Server sends PUBLISH Packets to the Client
 * in order to forward Application Messages that were published to Topics that match these Subscriptions. The SUBSCRIBE
 * Packet also specifies (for each Subscription) the maximum QoS with which the Server can send Application Messages to
 * the Client.
 */
final class Subscribe extends ProtocolBase implements WritableContentInterface
{
    use /** @noinspection TraitsPropertiesConflictsInspection */
        WritableContent;
    use PacketIdentifierFunctionality;
    use TopicFilterFunctionality;

    private const CONTROL_PACKET_VALUE = 8;

    /**
     * Indicates whether to continue the loop or break it at any point, cleanly without disconnecting from the broker
     * @var bool
     */
    private $shouldLoop = true;

    protected function initializeObject(): ProtocolBase
    {
        $this->topics = new SplQueue();
        return parent::initializeObject();
    }

    /**
     * @return string
     * @throws OutOfRangeException
     * @throws Exception
     */
    public function createVariableHeader(): string
    {
        // Subscribe must always send a 2 flag
        $this->specialFlags = 2;
        return $this->getPacketIdentifierBinaryRepresentation();
    }

    /**
     * @return string
     * @throws MustContainTopic
     * @throws OutOfRangeException
     */
    public function createPayload(): string
    {
        $output = '';
        foreach ($this->getTopics() as $topic) {
            // chr on QoS level is safe because it will create an 8-bit flag where the first 6 are only 0's
            $output .= $this->createUTF8String($topic->getTopicFilter()) . chr($topic->getTopicFilterQoSLevel());
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
     * Performs a check on the socket connection and returns either the contents or an empty object
     *
     * @param ClientInterface $client
     * @return ReadableContentInterface
     * @throws DomainException
     * @throws NotConnected
     * @throws NoConnectionParametersDefined
     */
    public function checkForEvent(ClientInterface $client): ReadableContentInterface
    {
        $this->checkPingTime($client);
        $publishPacketControlField = $client->readBrokerData(1);
        $eventManager = new EventManager($this->logger);

        if ((ord($publishPacketControlField) & 255) > 0) {
            $this->logger->debug('Event received', [
                'ordValue' => ord($publishPacketControlField) & 255,
                'length' => strlen($publishPacketControlField)
            ]);
            return $eventManager->analyzeHeaders($publishPacketControlField, $client);
        }

        $this->logger->debug('No valid publish packet control field found, returning empty response');
        return new EmptyReadableResponse($this->logger);
    }

    /**
     * Loop and yields different type of results back whenever they are available
     *
     * Be aware that 1 second is 1000000 microseconds
     *
     * @param ClientInterface $client
     * @param int $idleMicroseconds The amount of microseconds the watcher should wait before checking the socket again
     * @param callable|null $hookBeforeLoop
     * @return Generator
     * @throws NotConnected
     * @throws NoConnectionParametersDefined
     * @throws DomainException
     */
    public function loop(
        ClientInterface $client,
        int $idleMicroseconds = 100000,
        callable $hookBeforeLoop = null
    ): Generator {
        $this->shouldLoop = true;
        // First of all: subscribe
        $this->logger->debug('Beginning loop', ['idleMicroseconds' => $idleMicroseconds]);
        $readableContent = $client->processObject($this);

        // Allow the user to do certain stuff before looping, for example: an Unsubscribe
        if (is_callable($hookBeforeLoop)) {
            $this->logger->notice('Callable detected, executing', ['userFunctionName' => $hookBeforeLoop]);
            $hookBeforeLoop($this->logger);
        }

        while ($this->shouldLoop === true) {
            $this->logger->debug('++Loop++');
            if ($readableContent instanceof Publish) {
                $readableContent->performSpecialActions($client, $this);
                // Only if we receive a Publish event from the broker, yield the contents
                yield $readableContent->getMessage();
            } else {
                // Only wait for a certain amount of time if there was nothing in the queue
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
     * @param ClientInterface $client
     * @return bool
     */
    protected function checkPingTime(ClientInterface $client): bool
    {
        if ($client->isItPingTime()) {
            $this->logger->info('Pinging is needed, sending PingReq');
            $client->processObject(new PingReq($this->logger));
        }

        return true;
    }
}
