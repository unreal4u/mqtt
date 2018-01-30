<?php

declare(strict_types = 1);

namespace unreal4u\MQTT\DataTypes;

/**
 * This Value Object will always contain a valid topic name.
 */
final class TopicName
{
    /**
     *
     * @var string
     */
    private $topicName;

    /**
     * Topic name constructor.
     *
     * @param string $topicName
     *
     * @throws \OutOfBoundsException
     * @throws \InvalidArgumentException
     */
    public function __construct(string $topicName)
    {
        if ($topicName === '') {
            throw new \InvalidArgumentException('Topic name must be at least 1 character long');
        }

        if (\strlen($topicName) > 65535) {
            throw new \OutOfBoundsException('Topic name can not exceed 65535 bytes');
        }

        $this->topicName = $topicName;
    }

    /**
     * Gets the current QoS level
     *
     * @return int
     */
    public function getTopicName(): string
    {
        return $this->topicName;
    }
}
