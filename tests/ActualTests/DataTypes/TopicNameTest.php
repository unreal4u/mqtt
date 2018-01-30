<?php

declare(strict_types=1);

namespace tests\unreal4u\MQTT\Application;

use PHPUnit\Framework\TestCase;
use unreal4u\MQTT\DataTypes\TopicName;

class TopicNameTest extends TestCase
{
    public function test_emptyTopicName()
    {
        $this->expectException(\InvalidArgumentException::class);
        new TopicName('');
    }

    public function test_tooBigTopicName()
    {
        $this->expectException(\OutOfBoundsException::class);
        new TopicName(str_repeat('-', 65537));
    }

    public function provider_validQoSLevels(): array
    {
        $mapValues[] = ['First-topic-name'];
        $mapValues[] = ['𠜎𠜱𠝹𠱓'];
        $mapValues[] = ['Föllinge'];
        $mapValues[] = ['/Föllinge/First-topic-name/𠜎𠜱𠝹𠱓/normal'];

        return $mapValues;
    }

    /**
     * @dataProvider provider_validQoSLevels
     * @param int $level
     */
    public function test_validQoSLevels(string $topic)
    {
        $topicName = new TopicName($topic);
        $this->assertSame($topic, $topicName->getTopicName());
    }
}
