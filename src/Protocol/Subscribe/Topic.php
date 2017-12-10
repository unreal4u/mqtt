<?php

declare(strict_types=1);

namespace unreal4u\MQTT\Protocol\Subscribe;

use unreal4u\MQTT\Application\PayloadInterface;
use unreal4u\MQTT\Application\SimplePayload;
use unreal4u\MQTT\Exceptions\InvalidQoSLevel;

/**
 * When the client wants to subscribe to a topic, this is done by adding a topic filter.
 */
final class Topic
{
    /**
     * The 1st byte can contain some bits
     *
     * The order of these flags are:
     *
     *   7-6-5-4-3-2-1-0
     * b'0-0-0-0-0-0-0-0'
     *
     * Bit 7-4: Control packet value ID (3 for PUBLISH)
     * Bit 3: Duplicate delivery of a PUBLISH Control Packet
     * Bit 2 & 1: PUBLISH Quality of Service
     * Bit 0: PUBLISH Retain flag
     *
     * @see http://docs.oasis-open.org/mqtt/mqtt/v3.1.1/os/mqtt-v3.1.1-os.html#_Table_2.2_-
     * @var string
     */
    private $topicName = '';

    /**
     * The QoS lvl, choose between 0 and 2
     * @var int
     */
    private $qosLevel = 0;

    /**
     * Allows for a custom payload type per topic, defaults to SimplePayload
     *
     * @TODO This is still work in progress and may change in the future
     *
     * @see SimplePayload
     * @var PayloadInterface
     */
    private $payloadType;

    /**
     * Topic constructor.
     * @param string $topicName
     * @param int $qosLevel
     * @throws \unreal4u\MQTT\Exceptions\InvalidQoSLevel
     * @throws \InvalidArgumentException
     */
    public function __construct(string $topicName, int $qosLevel = 0)
    {
        $this
            ->setTopicName($topicName)
            ->setQoSLevel($qosLevel)
            ->setPayloadType(new SimplePayload());
    }

    /**
     * Contains the name of the Topic Filter
     *
     * @param string $topicName
     * @return Topic
     * @throws \InvalidArgumentException
     */
    private function setTopicName(string $topicName): Topic
    {
        if ($topicName === '') {
            throw new \InvalidArgumentException('Topic name must be set');
        }

        $this->topicName = $topicName;

        return $this;
    }

    /**
     * Requested QoS level is the maximum QoS level at which the Server can send Application Messages to the Client
     *
     * @param int $qosLevel
     * @return Topic
     * @throws \unreal4u\MQTT\Exceptions\InvalidQoSLevel
     */
    private function setQoSLevel(int $qosLevel): Topic
    {
        if ($qosLevel > 2 || $qosLevel < 0) {
            throw new InvalidQoSLevel('The provided QoS level is invalid');
        }

        $this->qosLevel = $qosLevel;

        return $this;
    }

    /**
     * Allows to set a different payload type for each topic
     *
     * @param PayloadInterface $payloadType
     * @return Topic
     */
    public function setPayloadType(PayloadInterface $payloadType): Topic
    {
        $this->payloadType = $payloadType;

        return $this;
    }

    /**
     * @return string
     */
    public function getTopicName(): string
    {
        return $this->topicName;
    }

    /**
     * @return int
     */
    public function getTopicQoSLevel(): int
    {
        return $this->qosLevel;
    }

    /**
     * @return PayloadInterface
     */
    public function getPayloadType(): PayloadInterface
    {
        return $this->payloadType;
    }
}
