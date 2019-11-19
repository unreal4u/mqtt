<?php

declare(strict_types=1);

namespace tests\unreal4u\MQTT\DataTypes;

use PHPUnit\Framework\TestCase;
use unreal4u\MQTT\DataTypes\TopicName;

class TopicNameTest extends TestCase
{
    public function testValidTopicName(): void
    {
        $topicName = new TopicName('a valid topic name');
        $this->assertSame('a valid topic name', $topicName->getTopicName());
        $this->assertSame('a valid topic name', (string)$topicName);
    }

    public function testWildcardInTopicName(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        new TopicName('invalidTopic/#/Name');
    }
}
