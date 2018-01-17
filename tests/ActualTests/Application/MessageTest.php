<?php

declare(strict_types=1);

namespace tests\unreal4u\MQTT\Application;

use PHPUnit\Framework\TestCase;
use unreal4u\MQTT\Application\Message;
use unreal4u\MQTT\Exceptions\InvalidQoSLevel;
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
        $this->message->setTopicName('Set up a topic');

        $this->expectException(MessageTooBig::class);
        $this->message->validateMessage();
    }

    public function test_invalidQoSLevel()
    {
        $this->expectException(InvalidQoSLevel::class);
        $this->message->setQoSLevel(-1);
    }

    public function provider_validQoSLevels(): array
    {
        $mapValues[] = [0];
        $mapValues[] = [1];
        $mapValues[] = [2];

        return $mapValues;
    }

    /**
     * @dataProvider provider_validQoSLevels
     * @param int $level
     */
    public function test_validQoSLevels(int $level)
    {
        $this->message->setQoSLevel($level);
        $this->assertSame($level, $this->message->getQoSLevel());
    }
}
