<?php

declare(strict_types=1);

namespace unreal4u\MQTT\Application;

use unreal4u\MQTT\DataTypes\QoSLevel;

/**
 * When the client wants to subscribe to a topic, this is done by adding a topic filter.
 */
final class Topic
{
    /**
     * The Topic Name identifies the information channel to which payload data is published.
     *
     * @see http://docs.oasis-open.org/mqtt/mqtt/v3.1.1/os/mqtt-v3.1.1-os.html#_Toc398718106
     * @var string
     */
    private $topicName;

    /**
     * The QoS lvl of this topic
     *
     * NOTE: Setting a QoS level where it is not needed (basically anything apart from a subscription) will have no
     * effect at all, as the QoS level is set not on a Topic level, but on a Message level instead.
     *
     * @var QoSLevel
     */
    private $qosLevel = 0;

    /**
     * Topic constructor.
     * @param string $topicName
     * @param QoSLevel $qosLevel
     * @throws \OutOfBoundsException
     * @throws \unreal4u\MQTT\Exceptions\InvalidQoSLevel
     * @throws \InvalidArgumentException
     */
    public function __construct(string $topicName, QoSLevel $qosLevel = null)
    {
        if ($qosLevel === null) {
            // QoSLevel defaults at 0
            $qosLevel = new QoSLevel(0);
        }

        $this
            ->setTopicName($topicName)
            ->setQoSLevel($qosLevel);
    }

    /**
     * Contains the name of the Topic Filter
     *
     * @param string $topicName
     * @return Topic
     * @throws \OutOfBoundsException
     * @throws \InvalidArgumentException
     */
    private function setTopicName(string $topicName): self
    {
        if ($topicName === '') {
            throw new \InvalidArgumentException('Topic name must be at least 1 character long');
        }

        if (\strlen($topicName) > 65535) {
            throw new \OutOfBoundsException('Topic name can not exceed 65535 bytes');
        }

        $this->topicName = $topicName;
        return $this;
    }

    /**
     * Requested QoS level is the maximum QoS level at which the Server can send Application Messages to the Client
     *
     * @param QoSLevel $qosLevel
     * @return Topic
     */
    private function setQoSLevel(QoSLevel $qosLevel): self
    {
        $this->qosLevel = $qosLevel;

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
        return $this->qosLevel->getQoSLevel();
    }
}
