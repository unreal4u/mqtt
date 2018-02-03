<?php

declare(strict_types=1);

namespace tests\unreal4u\MQTT\Application;

use PHPUnit\Framework\TestCase;
use unreal4u\MQTT\Application\Topic;

class TopicTest extends TestCase
{
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
