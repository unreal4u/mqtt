<?php

declare(strict_types=1);

namespace tests\unreal4u\MQTT\Application;

use PHPUnit\Framework\TestCase;
use unreal4u\MQTT\Application\Message;
use unreal4u\MQTT\Exceptions\MissingTopicName;

class MessageTest extends TestCase
{
    public function test_noTopicName()
    {
        $message = new Message();
        $this->expectException(MissingTopicName::class);
        $message->validateMessage();
    }
}
