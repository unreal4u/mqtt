<?php

declare(strict_types=1);

namespace unreal4u\MQTT\DataTypes;

use unreal4u\MQTT\Internals\GeneralTopicRules;

/**
 * When the client wants to send a message to a topic, this is done by adding a topic NAME.
 */
final class TopicName extends GeneralTopicRules
{
    /**
     * The TopicName identifies the information channel to which payload data is published.
     *
     * @see http://docs.oasis-open.org/mqtt/mqtt/v3.1.1/os/mqtt-v3.1.1-os.html#_Toc398718106
     * @var string
     */
    private $topicName;

    /**
     * TopicName constructor.
     * @param string $topicName
     * @throws \OutOfBoundsException
     * @throws \unreal4u\MQTT\Exceptions\InvalidQoSLevel
     * @throws \InvalidArgumentException
     */
    public function __construct(string $topicName)
    {
        $this->setTopicName($topicName);
    }

    /**
     * Contains the name of the TopicFilter Filter
     *
     * @param string $topicName
     * @return TopicName
     * @throws \OutOfBoundsException
     * @throws \InvalidArgumentException
     */
    private function setTopicName(string $topicName): self
    {
        $this->generalRulesCheck($topicName);

        // A topic name has some additional checks, as no wildcard characters are allowed
        if (strpbrk($topicName, '#+') !== false) {
            throw new \InvalidArgumentException('Topic names can not contain wildcard characters');
        }

        $this->topicName = $topicName;
        return $this;
    }

    /**
     * @return string
     */
    public function getTopicName(): string
    {
        return $this->topicName;
    }

    public function __toString()
    {
        return $this->getTopicName();
    }
}
