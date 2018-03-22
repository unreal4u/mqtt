<?php

declare(strict_types=1);

namespace unreal4u\MQTT\DataTypes;

use unreal4u\MQTT\Exceptions\MessageTooBig;

final class Message
{
    /**
     * This field indicates the level of assurance for delivery of an Application Message. Can be 0, 1 or 2
     *
     * 0: At most once delivery (default)
     * 1: At least once delivery
     * 2: Exactly once delivery
     *
     * @var QoSLevel
     */
    private $qosLevel;

    /**
     * @var string
     */
    private $payload;

    /**
     * If the RETAIN flag is set to 1, in a PUBLISH Packet sent by a Client to a Server, the Server MUST store the
     * Application Message and its QoS, so that it can be delivered to future subscribers whose subscriptions match its
     * topic name
     * @var bool
     */
    private $isRetained = false;

    /**
     * The Topic Name identifies the information channel to which payload data is published
     * @var Topic
     */
    private $topic;

    /**
     * Message constructor.
     * @param string $payload
     * @param Topic $topic
     * @throws \unreal4u\MQTT\Exceptions\MessageTooBig
     */
    public function __construct(string $payload, Topic $topic)
    {
        $this->topic = $topic;
        if (mb_strlen($payload) > 65535) {
            throw new MessageTooBig('Message payload can not exceed 65535 characters!');
        }

        $this->payload = $payload;
    }

    /**
     * Sets the QoS level to the indicated value. Must be 0, 1 or 2.
     *
     * @param QoSLevel $level
     * @return Message
     */
    public function setQoSLevel(QosLevel $level): Message
    {
        $this->qosLevel = $level;
        return $this;
    }

    /**
     * Sets the retain flag to the given value
     *
     * @param bool $flag Set to true if message should be retained, false otherwise (default)
     * @return Message
     */
    public function setRetainFlag(bool $flag): Message
    {
        $this->isRetained = $flag;
        return $this;
    }

    public function getPayload(): string
    {
        return $this->payload;
    }

    /**
     * Gets the topic name
     *
     * @return string
     */
    public function getTopicName(): string
    {
        return $this->topic->getTopicName();
    }

    /**
     * Gets the current QoS level
     *
     * @return int
     * @throws \unreal4u\MQTT\Exceptions\InvalidQoSLevel
     */
    public function getQoSLevel(): int
    {
        if ($this->qosLevel === null) {
            // QoSLevel defaults at 0
            $this->qosLevel = new QoSLevel(0);
        }
        return $this->qosLevel->getQoSLevel();
    }

    /**
     * Gets the set retain flag
     *
     * @return bool
     */
    public function isRetained(): bool
    {
        return $this->isRetained;
    }
}
