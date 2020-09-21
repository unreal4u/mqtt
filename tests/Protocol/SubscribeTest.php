<?php

declare(strict_types=1);

namespace tests\unreal4u\MQTT;

use PHPUnit\Framework\TestCase;
use tests\unreal4u\MQTT\Mocks\ClientMock;
use unreal4u\MQTT\Application\EmptyReadableResponse;
use unreal4u\MQTT\DataTypes\PacketIdentifier;
use unreal4u\MQTT\DataTypes\TopicFilter;
use unreal4u\MQTT\DebugTools;
use unreal4u\MQTT\Protocol\PingReq;
use unreal4u\MQTT\Protocol\PingResp;
use unreal4u\MQTT\Protocol\Subscribe;

class SubscribeTest extends TestCase
{
    /**
     * @var Subscribe
     */
    private $subscribe;

    protected function setUp(): void
    {
        $this->subscribe = new Subscribe();
        parent::setUp();
    }

    public function testShouldExpectAnswer(): void
    {
        $this->assertTrue($this->subscribe->shouldExpectAnswer());
    }

    public function testEmptyEvent(): void
    {
        $clientMock = new ClientMock();

        $result = $this->subscribe->checkForEvent($clientMock);
        $this->assertTrue($clientMock->isItPingTimeWasCalled());
        $this->assertTrue($clientMock->readBrokerDataWasCalled());
        $this->assertInstanceOf(EmptyReadableResponse::class, $result);
    }

    public function testPingResponseReturned(): void
    {
        $clientMock = new ClientMock();
        $clientMock->returnSpecificBrokerData(['0AA=']);

        $pingResponse = $this->subscribe->checkForEvent($clientMock);
        $this->assertInstanceOf(PingResp::class, $pingResponse);
    }

    public function testCreatePayload(): void
    {
        $this->subscribe->addTopics(new TopicFilter('test'));
        $this->assertSame('AAR0ZXN0Ag==', base64_encode($this->subscribe->createPayload()));
    }

    public function testCreateVariableHeaderWithFixedPacketIdentifier(): void
    {
        $this->subscribe->setPacketIdentifier(new PacketIdentifier(155));
        $this->assertSame('AJs=', base64_encode($this->subscribe->createVariableHeader()));
    }

    public function testCreateVariableHeaderWithRandomPacketIdentifier(): void
    {
        $output = $this->subscribe->createVariableHeader();
        $humanOutput = DebugTools::convertToBinaryRepresentation($output);
        $this->assertNotSame('0000000000000000', $humanOutput);
    }

    /**
     * @throws \ReflectionException
     */
    public function testItIsPingTime(): void
    {
        $clientMock = new ClientMock();
        $clientMock->setPingTime(true);

        $method = new \ReflectionMethod(Subscribe::class, 'checkPingTime');
        $method->setAccessible(true);

        $result = $method->invoke($this->subscribe, $clientMock);
        $this->assertSame(PingReq::class, $clientMock->processObjectWasCalledWithObjectType());
        $this->assertTrue($result);
    }

    public function testEmptyLoopAndBreakOutOfLoop(): void
    {
        $clientMock = new ClientMock();
        // Return a SUBACK and then a PUBLISH object with a message QoS lvl0
        $clientMock->returnSpecificBrokerData([
            'kAOcUwA=', // SubAck
            'MBQACWZpcnN0VGVzdOaxiUHlrZdCQw==', // QoS lvl0 message
        ]);

        foreach ($this->subscribe->loop($clientMock, 1) as $message) {
            $this->subscribe->breakLoop();
        }

        $this->assertSame(Subscribe::class, $clientMock->processObjectWasCalledWithObjectType());
        $this->assertSame('汉A字BC', $message->getPayload());
    }

    public function testCallableFunctionExecutesBeforeLoop(): void
    {
        $clientMock = new ClientMock();

        $callable = function () {
            $this->assertTrue(true);
        };

        $clientMock->returnSpecificBrokerData([
            'kAOcUwA=', // SubAck
            'MBQACWZpcnN0VGVzdOaxiUHlrZdCQw==', // QoS lvl0 message
        ]);

        foreach ($this->subscribe->loop($clientMock, 1, $callable) as $message) {
            $this->subscribe->breakLoop();
        }

        $this->assertSame(Subscribe::class, $clientMock->processObjectWasCalledWithObjectType());
        $this->assertSame('汉A字BC', $message->getPayload());
    }
}
