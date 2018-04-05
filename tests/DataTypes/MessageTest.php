<?php

declare(strict_types=1);

namespace tests\unreal4u\MQTT\DataTypes;

use PHPUnit\Framework\TestCase;
use unreal4u\MQTT\DataTypes\Message;
use unreal4u\MQTT\DataTypes\TopicName;
use unreal4u\MQTT\Exceptions\MessageTooBig;

class MessageTest extends TestCase
{
    public function test_messageTooBig()
    {
        $this->expectException(MessageTooBig::class);
        new Message(str_repeat('ö', 65536), new TopicName('Set up a topic'));
    }
}
