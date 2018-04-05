<?php

declare(strict_types=1);

namespace tests\unreal4u\MQTT;

use PHPUnit\Framework\TestCase;
use tests\unreal4u\MQTT\Mocks\ClientMock;
use unreal4u\MQTT\Application\EmptyReadableResponse;
use unreal4u\MQTT\DataTypes\Message;
use unreal4u\MQTT\DataTypes\TopicName;
use unreal4u\MQTT\DataTypes\PacketIdentifier;
use unreal4u\MQTT\DataTypes\QoSLevel;
use unreal4u\MQTT\Exceptions\InvalidRequest;
use unreal4u\MQTT\Protocol\PingReq;
use unreal4u\MQTT\Protocol\PubAck;
use unreal4u\MQTT\Protocol\Publish;
use unreal4u\MQTT\Protocol\PubRec;

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
        $this->message = new Message('Hello test world!', new TopicName('t'));
    }

    protected function tearDown()
    {
        parent::tearDown();
        $this->publish = null;
    }

    public function test_getOriginControlPacket()
    {
        $this->assertSame(0, $this->publish->getOriginControlPacket());
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
        $this->publish->setPacketIdentifier(new PacketIdentifier(1));
        $variableHeader = $this->publish->createVariableHeader();
        $this->assertSame('AAF0AAE=', base64_encode($variableHeader));
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
        $this->publish->setPacketIdentifier(new PacketIdentifier(1));
        $this->publish->createVariableHeader();
        /** @var PubAck $answer */
        $answer = $this->publish->expectAnswer(base64_decode('QAIAAQ=='), new ClientMock());
        $this->assertInstanceOf(PubAck::class, $answer);
        $this->assertSame($answer->getPacketIdentifier(), $this->publish->getPacketIdentifier());
    }

    public function test_QoSLevel2ExpectedAnswer()
    {
        $this->message->setQoSLevel(new QoSLevel(2));
        $this->publish->setMessage($this->message);
        $this->publish->setPacketIdentifier(new PacketIdentifier(111));
        $this->publish->createVariableHeader();
        /** @var PubAck $answer */
        $answer = $this->publish->expectAnswer(base64_decode('UAIAbw=='), new ClientMock());
        $this->assertInstanceOf(PubRec::class, $answer);
        $this->assertSame($answer->getPacketIdentifier(), $this->publish->getPacketIdentifier());
    }

    public function test_noPayloadException()
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->publish->createPayload();
    }

    public function test_goodPayload()
    {
        $this->publish->setMessage($this->message);
        $this->assertSame('Hello test world!', $this->publish->createPayload());
    }

    public function test_getMessage()
    {
        $this->publish->setMessage($this->message);
        $objectMessage = $this->publish->getMessage();
        $this->assertSame($this->message, $objectMessage);
    }

    public function provider_calculateIncomingQoSLevel(): array
    {
        $mapValues[] = [48, 0];
        $mapValues[] = [50, 1];
        $mapValues[] = [58, 1]; // Redelivery of QoS level 1 type message
        $mapValues[] = [52, 2];

        return $mapValues;
    }

    /**
     * @dataProvider provider_calculateIncomingQoSLevel
     * @param int $bitString
     * @param int $expectedQoS
     * @throws \ReflectionException
     */
    public function test_calculateIncomingQoSLevel(int $bitString, int $expectedQoS)
    {
        $method = new \ReflectionMethod(Publish::class, 'determineIncomingQoSLevel');
        $method->setAccessible(true);

        $qosLevel = $method->invoke(new Publish(), $bitString);
        $this->assertSame($expectedQoS, $qosLevel->getQoSLevel());
    }

    public function provider_analyzeFirstByte(): array
    {
        $mapValues[] = [48, new QoSLevel(0), false, false];
        $mapValues[] = [50, new QoSLevel(1), false, false];
        $mapValues[] = [51, new QoSLevel(1), true, false];
        $mapValues[] = [58, new QoSLevel(1), false, true];
        $mapValues[] = [59, new QoSLevel(1), true, true];
        $mapValues[] = [52, new QoSLevel(2), false, false];
        $mapValues[] = [53, new QoSLevel(2), true, false];

        return $mapValues;
    }

    /**
     * @dataProvider provider_analyzeFirstByte
     * @param int $firstByte
     * @param QoSLevel $qoSLevel
     * @param bool $isRetained
     * @param bool $isRedelivery
     * @throws \ReflectionException
     */
    public function test_analyzeFirstByte(int $firstByte, QoSLevel $qoSLevel, bool $isRetained, bool $isRedelivery)
    {
        $method = new \ReflectionMethod(Publish::class, 'analyzeFirstByte');
        $method->setAccessible(true);

        $this->publish->setMessage($this->message);
        $publishObject = $method->invoke($this->publish, $firstByte, $qoSLevel);
        $this->assertSame($isRetained, $publishObject->getMessage()->isRetained());
        $this->assertSame($isRedelivery, $publishObject->isRedelivery);
    }

    public function provider_performSpecialActions(): array
    {
        $mapValues[] = [0, 126, ''];
        $mapValues[] = [1, 127, PubAck::class];
        $mapValues[] = [2, 128, PubRec::class];

        return $mapValues;
    }

    /**
     * @dataProvider provider_performSpecialActions
     * @param int $QoSLevel
     * @param int $packetIdentifier
     * @param string $expectedClassType
     * @throws \unreal4u\MQTT\Exceptions\ServerClosedConnection
     */
    public function test_performSpecialActions(int $QoSLevel, int $packetIdentifier, string $expectedClassType)
    {
        $clientMock = new ClientMock();
        $this->message->setQoSLevel(new QoSLevel($QoSLevel));
        // Emulate an incoming message
        $this->publish->setMessage($this->message);
        $this->publish->setPacketIdentifier(new PacketIdentifier($packetIdentifier));

        $result = $this->publish->performSpecialActions($clientMock, new PingReq());
        $this->assertTrue($result);
        $this->assertSame($expectedClassType, $clientMock->processObjectWasCalledWithObjectType());
    }

    /**
     * @throws \ReflectionException
     */
    public function test_composePubRecAnswer()
    {
        $this->publish->setPacketIdentifier(new PacketIdentifier(123));
        $method = new \ReflectionMethod(Publish::class, 'composePubRecAnswer');
        $method->setAccessible(true);

        /** @var PubRec $pubRec */
        $pubRec = $method->invoke($this->publish);
        $this->assertInstanceOf(PubRec::class, $pubRec);
        $this->assertSame(123, $pubRec->getPacketIdentifier());
    }

    /**
     * @throws \ReflectionException
     */
    public function test_composePubAckAnswer()
    {
        $this->publish->setPacketIdentifier(new PacketIdentifier(124));
        $method = new \ReflectionMethod(Publish::class, 'composePubAckAnswer');
        $method->setAccessible(true);

        /** @var PubRec $pubRec */
        $pubAck = $method->invoke($this->publish);
        $this->assertInstanceOf(PubAck::class, $pubAck);
        $this->assertSame(124, $pubAck->getPacketIdentifier());
    }

    /**
     * @throws \ReflectionException
     */
    public function test_checkForValidPacketIdentifier()
    {
        $method = new \ReflectionMethod(Publish::class, 'checkForValidPacketIdentifier');
        $method->setAccessible(true);

        $this->expectException(InvalidRequest::class);
        $method->invoke($this->publish);
    }

    public function provider_completePossibleIncompleteMessage(): array
    {
        // First case: 1 byte and the rest of the message
        $mapValues[] = ['MA==', 'FAAJZmlyc3RUZXN05rGJQeWtl0JD', 'MBQACWZpcnN0VGVzdOaxiUHlrZdCQw=='];
        // Second case: 4 bytes already in rawHeaders, the rest still to be provided
        $mapValues[] = ['MBQACQ==', 'Zmlyc3RUZXN05rGJQeWtl0JD', 'MBQACWZpcnN0VGVzdOaxiUHlrZdCQw=='];
        // Edge-case: all but 1 byte already in rawHeaders
        $mapValues[] = ['MBQACWZpcnN0VGVzdOaxiUHlrZdC', 'Qw==', 'MBQACWZpcnN0VGVzdOaxiUHlrZdCQw=='];
        // Edge-case: no bytes left
        $mapValues[] = ['MBQACWZpcnN0VGVzdOaxiUHlrZdCQw==', '', 'MBQACWZpcnN0VGVzdOaxiUHlrZdCQw=='];

        return $mapValues;
    }

    /**
     * @dataProvider provider_completePossibleIncompleteMessage
     * @param string $firstBytes
     * @param string $append
     * @param string $expectedOutput
     * @throws \ReflectionException
     */
    public function test_completePossibleIncompleteMessage(string $firstBytes, string $append, string $expectedOutput)
    {
        $method = new \ReflectionMethod(Publish::class, 'completePossibleIncompleteMessage');
        $method->setAccessible(true);

        $clientMock = new ClientMock();
        $clientMock->returnSpecificBrokerData([$append]);
        $output = base64_encode($method->invoke($this->publish, base64_decode($firstBytes), $clientMock));

        $this->assertSame($expectedOutput, $output);
        $this->assertTrue($clientMock->readBrokerDataWasCalled());
    }

    public function provider_fillObject(): array
    {
        // QoS 0 with UTF-8 characters
        $mapVal[] = ['MBQACWZpcnN0VGVzdOaxiUHlrZdCQw==', 0, 'firstTest', '汉A字BC', 0];
        // QoS 1 with packetIdentifier 10
        $mapVal[] = ['MiIACWZpcnN0VGVzdAAKSGVsbG8gd29ybGQhISAoMSAvIDMp', 1, 'firstTest', 'Hello world!! (1 / 3)', 10];
        // QoS 1 with packetIdentigier 15 and different message
        $mapVal[] = ['MiIACWZpcnN0VGVzdAAPSGVsbG8gd29ybGQhISAoMyAvIDMp', 1, 'firstTest', 'Hello world!! (3 / 3)', 15];
        // QoS 2 with packetIdentifier 16
        $mapVal[] = ['NCIACWZpcnN0VGVzdAAQSGVsbG8gd29ybGQhISAoMSAvIDEp', 2, 'firstTest', 'Hello world!! (1 / 1)', 16];

        return $mapVal;
    }

    /**
     * @dataProvider provider_fillObject
     * @param string $rawData
     * @param int $expectedQoSLevel
     * @param string $expectedTopicName
     * @param string $expectedMessageContent
     * @param int|null $expectedPacketIdentifier
     */
    public function test_fillObject(
        string $rawData,
        int $expectedQoSLevel,
        string $expectedTopicName,
        string $expectedMessageContent,
        int $expectedPacketIdentifier
    ) {
        $this->publish->fillObject(base64_decode($rawData), new ClientMock());
        $message = $this->publish->getMessage();
        $this->assertSame($expectedQoSLevel, $message->getQoSLevel());
        $this->assertSame($expectedTopicName, $message->getTopicName());
        $this->assertSame($expectedMessageContent, $message->getPayload());
        if ($expectedPacketIdentifier !== 0) {
            $this->assertSame($expectedPacketIdentifier, $this->publish->getPacketIdentifier());
        }
    }
}
