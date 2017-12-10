<?php

declare(strict_types=1);

namespace tests\unreal4u\MQTT\Subscribe;

use PHPUnit\Framework\TestCase;
use unreal4u\MQTT\Application\SimplePayload;
use unreal4u\MQTT\Exceptions\InvalidQoSLevel;
use unreal4u\MQTT\Protocol\Subscribe\Topic;

class TopicTest extends TestCase
{
    public function test_createDefault()
    {
        $topic = new Topic('a topic');
        $this->assertSame('a topic', $topic->getTopicName());
        $this->assertSame(0, $topic->getTopicQoSLevel());
        $this->assertInstanceOf(SimplePayload::class, $topic->getPayloadType());
    }

    public function test_noTopicName()
    {
        $this->expectException(\InvalidArgumentException::class);
        new Topic('');
    }

    public function test_QoSLevel1()
    {
        $topic = new Topic('a topic', 1);
        $this->assertSame('a topic', $topic->getTopicName());
        $this->assertSame(1, $topic->getTopicQoSLevel());
        $this->assertInstanceOf(SimplePayload::class, $topic->getPayloadType());
    }

    public function test_QoSLevel2()
    {
        $topic = new Topic('a topic', 2);
        $this->assertSame('a topic', $topic->getTopicName());
        $this->assertSame(2, $topic->getTopicQoSLevel());
        $this->assertInstanceOf(SimplePayload::class, $topic->getPayloadType());
    }

    public function test_invalidQoSLevel()
    {
        $this->expectException(InvalidQoSLevel::class);
        new Topic('a topic', -1);
    }
}
