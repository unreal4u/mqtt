<?php

declare(strict_types=1);

namespace unreal4u\MQTT\Internals;

use unreal4u\MQTT\DataTypes\Topic;
use unreal4u\MQTT\Exceptions\MustContainTopic;

/**
 * Trait ReadableContent
 * @package unreal4u\MQTT\Internals
 */
trait TopicFunctionality
{
    /**
     * The list of topics, being a SplQueue ensures the order is correct
     * @var \SplQueue
     */
    private $topics;

    /**
     * Unordered list of topic names in order to avoid duplicates
     * @var array
     */
    private $topicHashTable = [];

    /**
     * A subscription is based on filters, this function allows us to pass on filters
     *
     * @param Topic[] $topics
     * @return self
     */
    final public function addTopics(Topic ...$topics): self
    {
        foreach ($topics as $topic) {
            // Frequently used: topicName, make it an apart variable for easy access
            $topicName = $topic->getTopicName();
            if (\in_array($topicName, $this->topicHashTable, true) === false) {
                // Personally, I find this hacky as hell. However, using the SPL library there seems to be no other way
                $this->topicHashTable[] = $topicName;
                $this->topics->enqueue($topic);
            }
        }

        return $this;
    }

    /**
     * Returns the current number of topics
     *
     * @return int
     */
    private function getNumberOfTopics(): int
    {
        return $this->topics->count();
    }

    /**
     * Returns the topics in the order they were inserted / requested
     *
     * The order is important because SUBACK will return the status code for each topic in this order, without
     * explicitly identifying which topic is which
     *
     * @return \Generator|Topic[]
     * @throws \unreal4u\MQTT\Exceptions\MustContainTopic
     */
    private function getTopics(): \Generator
    {
        if ($this->getNumberOfTopics() === 0) {
            throw new MustContainTopic('Before getting a topic, you should set it');
        }

        for ($this->topics->rewind(); $this->topics->valid(); $this->topics->next()) {
            yield $this->topics->current();
        }
    }
}
