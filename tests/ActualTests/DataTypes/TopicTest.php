<?php

declare(strict_types=1);

namespace tests\unreal4u\MQTT\DataTypes;

use PHPUnit\Framework\TestCase;
use unreal4u\MQTT\DataTypes\Topic;
use unreal4u\MQTT\DataTypes\QoSLevel;

class TopicTest extends TestCase
{
    public function test_createDefault()
    {
        $topic = new Topic('a topic', new QoSLevel(0));
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
        $topic = new Topic('a topic');
        $this->assertSame('a topic', $topic->getTopicName());
        $this->assertSame(2, $topic->getTopicQoSLevel());
    }

    public function test_emptyTopicName()
    {
        $this->expectException(\InvalidArgumentException::class);
        new Topic('');
    }

    public function test_tooBigTopicName()
    {
        $this->expectException(\OutOfBoundsException::class);
        new Topic(str_repeat('-', 65537));
    }

    public function provider_validTopicNames(): array
    {
        $mapValues[] = ['First-topic-name'];
        $mapValues[] = ['𠜎𠜱𠝹𠱓'];
        $mapValues[] = ['Föllinge'];
        $mapValues[] = ['/Föllinge/First-topic-name1234/𠜎𠜱𠝹𠱓/normal'];

        return $mapValues;
    }

    /**
     * @dataProvider provider_validTopicNames
     * @param string $topicName
     */
    public function test_validTopicNames(string $topicName)
    {
        $topic = new Topic($topicName);
        $this->assertSame($topicName, $topic->getTopicName());
        $this->assertSame(2, $topic->getTopicQoSLevel());
    }
}
