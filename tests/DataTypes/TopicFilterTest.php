<?php

declare(strict_types=1);

namespace tests\unreal4u\MQTT\DataTypes;

use PHPUnit\Framework\TestCase;
use unreal4u\MQTT\DataTypes\TopicFilter;
use unreal4u\MQTT\DataTypes\QoSLevel;

class TopicFilterTest extends TestCase
{
    public function test_createDefault()
    {
        $topic = new TopicFilter('a topic', new QoSLevel(0));
        $this->assertSame('a topic', $topic->getTopicFilter());
        $this->assertSame(0, $topic->getTopicFilterQoSLevel());
    }

    public function test_QoSLevel1()
    {
        $topic = new TopicFilter('a topic', new QoSLevel(1));
        $this->assertSame('a topic', $topic->getTopicFilter());
        $this->assertSame(1, $topic->getTopicFilterQoSLevel());
    }

    public function test_QoSLevel2()
    {
        $topic = new TopicFilter('a topic');
        $this->assertSame('a topic', $topic->getTopicFilter());
        $this->assertSame(2, $topic->getTopicFilterQoSLevel());
    }

    public function test_emptyTopicName()
    {
        $this->expectException(\InvalidArgumentException::class);
        new TopicFilter('');
    }

    public function test_terminationCharacterPresentInTopicFilter()
    {
        $this->expectException(\InvalidArgumentException::class);
        new TopicFilter('hello/world' . \chr(0) . '/Yep');
    }

    public function test_tooBigTopicName()
    {
        $this->expectException(\OutOfBoundsException::class);
        new TopicFilter(str_repeat('-', 65537));
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
        $topic = new TopicFilter($topicName);
        $this->assertSame($topicName, $topic->getTopicFilter());
        $this->assertSame(2, $topic->getTopicFilterQoSLevel());
    }
}
