<?php

declare(strict_types=1);

namespace tests\unreal4u\MQTT\Subscribe;

use PHPUnit\Framework\TestCase;
use unreal4u\MQTT\Application\Topic;
use unreal4u\MQTT\DataTypes\QoSLevel;

class TopicTest extends TestCase
{
    public function test_createDefault()
    {
        $topic = new Topic('a topic');
        $this->assertSame('a topic', $topic->getTopicName());
        $this->assertSame(0, $topic->getTopicQoSLevel());
    }

    public function test_QoSLevel1()
    {
        $topic = new Topic('a topic', new QoSLevel(1));
        $this->assertSame('a topic', $topic->getTopicName());
        $this->assertSame(1, $topic->getTopicQoSLevel());
    }

    public function test_QoSLevel2()
    {
        $topic = new Topic('a topic', new QoSLevel(2));
        $this->assertSame('a topic', $topic->getTopicName());
        $this->assertSame(2, $topic->getTopicQoSLevel());
    }
}
