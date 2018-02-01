<?php

declare(strict_types=1);

namespace tests\unreal4u\MQTT;

use PHPUnit\Framework\TestCase;
use tests\unreal4u\MQTT\Mocks\ClientMock;
use unreal4u\MQTT\Application\EmptyReadableResponse;
use unreal4u\MQTT\Application\Message;
use unreal4u\MQTT\Application\Topic;
use unreal4u\MQTT\DataTypes\QoSLevel;
use unreal4u\MQTT\Protocol\PubAck;
use unreal4u\MQTT\Protocol\Publish;

class PublishTest extends TestCase
{
    /**
     * @var Publish
     */
    private $publish;

    /**
     * @var Message
     */
    private $message;

    protected function setUp()
    {
        parent::setUp();
        $this->publish = new Publish();

        $this->message = new Message();
        $this->message->setPayload('Hello test world!');
        $this->message->setTopic(new Topic('t'));
    }

    protected function tearDown()
    {
        parent::tearDown();
        $this->publish = null;
    }

    public function test_throwExceptionNoMessageProvided()
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->publish->createVariableHeader();
    }

    public function test_publishBasicMessage()
    {
        $this->publish->setMessage($this->message);
        $variableHeader = $this->publish->createVariableHeader();
        $this->assertSame('AAF0', base64_encode($variableHeader));
    }

    public function test_PublishComplexMessage()
    {
        $this->message->setQoSLevel(new QoSLevel(1));
        $this->message->setRetainFlag(true);

        $this->publish->setMessage($this->message);
        $variableHeader = $this->publish->createVariableHeader();
        $this->assertSame('AAF0AAA=', base64_encode($variableHeader));
    }

    public function test_NoAnswerRequired()
    {
        $this->publish->setMessage($this->message);
        $this->assertFalse($this->publish->shouldExpectAnswer());
    }

    public function test_AnswerRequired()
    {
        $this->message->setQoSLevel(new QoSLevel(1));
        $this->publish->setMessage($this->message);
        $this->assertTrue($this->publish->shouldExpectAnswer());
    }

    public function test_emptyExpectedAnswer()
    {
        $this->publish->setMessage($this->message);
        $answer = $this->publish->expectAnswer('000', new ClientMock());
        $this->assertInstanceOf(EmptyReadableResponse::class, $answer);
    }

    public function test_QoSLevel1ExpectedAnswer()
    {
        $this->message->setQoSLevel(new QoSLevel(1));
        $this->publish->setMessage($this->message);
        $this->publish->packetIdentifier = 1;
        $this->publish->createVariableHeader();
        /** @var PubAck $answer */
        $answer = $this->publish->expectAnswer(base64_decode('QAIAAQ=='), new ClientMock());
        $this->assertInstanceOf(PubAck::class, $answer);
        $this->assertSame($answer->packetIdentifier, $this->publish->packetIdentifier);
    }
}
