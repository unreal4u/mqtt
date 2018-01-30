<?php

declare(strict_types=1);

namespace unreal4u\MQTT\Application;

use unreal4u\MQTT\DataTypes\QoSLevel;
use unreal4u\MQTT\Exceptions\MessageTooBig;
use unreal4u\MQTT\Exceptions\MissingTopicName;
use unreal4u\MQTT\Internals\ProtocolBase;

final class Message extends ProtocolBase
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
     * Will perform validation on the message before sending it to the MQTT broker
     *
     * @return Message
     * @throws \unreal4u\MQTT\Exceptions\MissingTopicName
     * @throws \unreal4u\MQTT\Exceptions\MessageTooBig
     */
    public function validateMessage(): Message
    {
        // Getter of topicname will validate whether the topic name is set and valid
        $this->getTopicName();

        if (mb_strlen($this->payload) > 65535) {
            $this->logger->error('Message payload exceeds 65535 bytes');
            throw new MessageTooBig('Message payload can not exceed 65535 bytes!');
        }

        return $this;
    }

    /**
     * Sets the actual payload to be sent to the message broker
     *
     * @param string $payload
     * @return Message
     */
    public function setPayload(string $payload): Message
    {
        $this->payload = $payload;
        return $this;
    }

    public function getPayload(): string
    {
        return $this->payload;
    }

    /**
     * Sets the QoS level to the indicated value. Must be 0, 1 or 2.
     *
     * @param QoSLevel $level
     * @return Message
     * @throws \unreal4u\MQTT\Exceptions\InvalidQoSLevel
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

    /**
     * Sets the topic name to the given value
     *
     * @param Topic $topic
     * @return Message
     */
    public function setTopic(Topic $topic): Message
    {
        $this->topic = $topic;
        return $this;
    }

    /**
     * Gets the topic name
     *
     * @return string
     * @throws \unreal4u\MQTT\Exceptions\MissingTopicName
     */
    public function getTopicName(): string
    {
        if ($this->topic === null) {
            throw new MissingTopicName('A topic must be set before calling getTopicName()');
        }

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
