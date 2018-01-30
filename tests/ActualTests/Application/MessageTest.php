<?php

declare(strict_types=1);

namespace tests\unreal4u\MQTT\Application;

use PHPUnit\Framework\TestCase;
use unreal4u\MQTT\Application\Message;
use unreal4u\MQTT\Application\Topic;
use unreal4u\MQTT\DataTypes\TopicName;
use unreal4u\MQTT\Exceptions\MessageTooBig;
use unreal4u\MQTT\Exceptions\MissingTopicName;

class MessageTest extends TestCase
{
    /**
     * @var Message
     */
    private $message;

    protected function setUp()
    {
        $this->message = new Message();
        parent::setUp();
    }

    protected function tearDown()
    {
        parent::tearDown();
        $this->message = null;
    }

    public function test_noTopicName()
    {
        $this->expectException(MissingTopicName::class);
        $this->message->validateMessage();
    }

    public function test_messageTooBig()
    {
        $this->message->setPayload(str_repeat('ö', 65536));
        $this->message->setTopic(new Topic(new TopicName('Set up a topic')));

        $this->expectException(MessageTooBig::class);
        $this->message->validateMessage();
    }
}
