<?php

declare(strict_types=1);

namespace unreal4u\MQTT\Application;

use unreal4u\MQTT\Exceptions\InvalidQoSLevel;
use unreal4u\MQTT\Exceptions\MessageTooBig;
use unreal4u\MQTT\Exceptions\MissingTopicName;

final class Message
{
    /**
     * This field indicates the level of assurance for delivery of an Application Message. Can be 0, 1 or 2
     *
     * 0: At most once delivery (default)
     * 1: At least once delivery
     * 2: Exactly once delivery
     *
     * @var int
     */
    private $qosLevel = 0;

    /**
     * @var PayloadInterface
     */
    private $payload;

    /**
     * If the RETAIN flag is set to 1, in a PUBLISH Packet sent by a Client to a Server, the Server MUST store the
     * Application Message and its QoS, so that it can be delivered to future subscribers whose subscriptions match its
     * topic name
     * @var bool
     */
    private $mustRetain = false;

    /**
     * The Topic Name identifies the information channel to which payload data is published
     * @var string
     */
    private $topicName = '';

    /**
     * Will perform validation on the message before sending it to the MQTT broker
     *
     * @return Message
     * @throws \unreal4u\MQTT\Exceptions\MissingTopicName
     * @throws \unreal4u\MQTT\Exceptions\MessageTooBig
     */
    public function validateMessage(): Message
    {
        if ($this->topicName === '') {
            throw new MissingTopicName('Topic can\'t be empty, please provide one');
        }

        $processedPayload = $this->payload->getProcessedPayload();
        if (mb_strlen($processedPayload) > 65535) {
            throw new MessageTooBig('Message payload can not exceed 65535 bytes!');
        }

        return $this;
    }

    /**
     * Sets the actual payload to be sent to the message broker
     *
     * @param PayloadInterface $payload
     * @return Message
     */
    public function setPayload(PayloadInterface $payload): Message
    {
        $this->payload = $payload;
        return $this;
    }

    public function getPayload(): string
    {
        return $this->payload->getProcessedPayload();
    }

    /**
     * Sets the QoS level to the indicated value. Must be 0, 1 or 2.
     *
     * @param int $level
     * @return Message
     * @throws \unreal4u\MQTT\Exceptions\InvalidQoSLevel
     */
    public function setQoSLevel(int $level): Message
    {
        if ($level > 2 || $level < 0) {
            throw new InvalidQoSLevel('The QoS level must be 0, 1 or 2');
        }

        $this->qosLevel = $level;
        return $this;
    }

    /**
     * Sets the retain flag to the given value
     *
     * @param bool $flag
     * @return Message
     */
    public function setRetainFlag(bool $flag): Message
    {
        $this->mustRetain = $flag;
        return $this;
    }

    /**
     * Sets the topic name to the given value
     *
     * @param string $topicName
     * @return Message
     */
    public function setTopicName(string $topicName): Message
    {
        $this->topicName = $topicName;
        return $this;
    }

    public function getTopicName(): string
    {
        return $this->topicName;
    }

    public function getQoSLevel(): int
    {
        return $this->qosLevel;
    }

    public function mustRetain(): bool
    {
        return $this->mustRetain;
    }
}
