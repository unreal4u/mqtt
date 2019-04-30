<?php

declare(strict_types=1);

namespace unreal4u\MQTT\DataTypes;

use unreal4u\MQTT\Internals\GeneralTopicRules;

/**
 * When the client wants to subscribe to a topic, this is done by adding a topic FILTER.
 */
final class TopicFilter extends GeneralTopicRules
{
    /**
     * The TopicFilter Name identifies the information channel to which payload data is being retrieved from.
     *
     * @see http://docs.oasis-open.org/mqtt/mqtt/v3.1.1/os/mqtt-v3.1.1-os.html#_Toc398718106
     * @var string
     */
    private $topicFilter;

    /**
     * The QoS lvl of this topic
     *
     * NOTE: The SUBSCRIBE Packet also specifies (for each Subscription) the maximum QoS with which the Server can send
     *       Application Messages to the Client. So even if the server has QoS lvl 2 messages in the queue, it will send
     *       them as QoS lvl 0 if we provide lvl 0 as the input for this function. Defaults to QoS lvl 2.
     *
     * @var QoSLevel
     */
    private $qosLevel;

    /**
     * TopicFilter constructor.
     * @param string $topicName
     * @param QoSLevel $qosLevel
     * @throws \OutOfBoundsException
     * @throws \unreal4u\MQTT\Exceptions\InvalidQoSLevel
     * @throws \InvalidArgumentException
     */
    public function __construct(string $topicName, QoSLevel $qosLevel = null)
    {
        if ($qosLevel === null) {
            // QoSLevel defaults at 2 (Enable maximum by default)
            $qosLevel = new QoSLevel(2);
        }

        $this
            ->setTopicName($topicName)
            ->setQoSLevel($qosLevel);
    }

    /**
     * Will validate and set the topic filter
     *
     * @param string $topicFilter
     * @return TopicFilter
     * @throws \OutOfBoundsException
     * @throws \InvalidArgumentException
     */
    private function setTopicName(string $topicFilter): self
    {
        $this->generalRulesCheck($topicFilter);

        $this->topicFilter = $topicFilter;
        return $this;
    }

    /**
     * Requested QoS level is the maximum QoS level at which the Server can send Application Messages to the Client
     *
     * @param QoSLevel $qosLevel
     * @return TopicFilter
     */
    private function setQoSLevel(QoSLevel $qosLevel): self
    {
        $this->qosLevel = $qosLevel;

        return $this;
    }

    /**
     * @return string
     */
    public function getTopicFilter(): string
    {
        return $this->topicFilter;
    }

    /**
     * @return int
     */
    public function getTopicFilterQoSLevel(): int
    {
        return $this->qosLevel->getQoSLevel();
    }
}
