<?php

declare(strict_types=1);

namespace tests\unreal4u\MQTT\DataTypes;

use PHPUnit\Framework\TestCase;
use unreal4u\MQTT\DataTypes\TopicFilter;
use unreal4u\MQTT\DataTypes\QoSLevel;

class TopicFilterTest extends TestCase
{
    public function testCreateDefault(): void
    {
        $topic = new TopicFilter('a topic', new QoSLevel(0));
        $this->assertSame('a topic', $topic->getTopicFilter());
        $this->assertSame(0, $topic->getTopicFilterQoSLevel());
    }

    public function testQoSLevel1(): void
    {
        $topic = new TopicFilter('a topic', new QoSLevel(1));
        $this->assertSame('a topic', $topic->getTopicFilter());
        $this->assertSame(1, $topic->getTopicFilterQoSLevel());
    }

    public function testQoSLevel2(): void
    {
        $topic = new TopicFilter('a topic');
        $this->assertSame('a topic', $topic->getTopicFilter());
        $this->assertSame(2, $topic->getTopicFilterQoSLevel());
    }

    public function testEmptyTopicName(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        new TopicFilter('');
    }

    public function testTerminationCharacterPresentInTopicFilter(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        new TopicFilter('hello/world' . \chr(0) . '/Yep');
    }

    public function testTooBigTopicName(): void
    {
        $this->expectException(\OutOfBoundsException::class);
        new TopicFilter(str_repeat('-', 65537));
    }

    public function providerValidTopicNames(): array
    {
        $mapValues[] = ['First-topic-name'];
        $mapValues[] = ['𠜎𠜱𠝹𠱓'];
        $mapValues[] = ['Föllinge'];
        $mapValues[] = ['/Föllinge/First-topic-name1234/𠜎𠜱𠝹𠱓/normal'];

        return $mapValues;
    }

    /**
     * @dataProvider providerValidTopicNames
     * @param string $topicName
     */
    public function testValidTopicNames(string $topicName): void
    {
        $topic = new TopicFilter($topicName);
        $this->assertSame($topicName, $topic->getTopicFilter());
        $this->assertSame(2, $topic->getTopicFilterQoSLevel());
    }
}
