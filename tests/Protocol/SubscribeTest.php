<?php

declare(strict_types=1);

namespace tests\unreal4u\MQTT;

use PHPUnit\Framework\TestCase;
use tests\unreal4u\MQTT\Mocks\ClientMock;
use unreal4u\MQTT\Application\EmptyReadableResponse;
use unreal4u\MQTT\DataTypes\PacketIdentifier;
use unreal4u\MQTT\DataTypes\Topic;
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

    protected function setUp()
    {
        $this->subscribe = new Subscribe();
        parent::setUp();
    }

    public function test_shouldExpectAnswer()
    {
        $this->assertTrue($this->subscribe->shouldExpectAnswer());
    }

    public function test_emptyEvent()
    {
        $clientMock = new ClientMock();

        $result = $this->subscribe->checkForEvent($clientMock);
        $this->assertTrue($clientMock->isItPingTimeWasCalled());
        $this->assertTrue($clientMock->readBrokerDataWasCalled());
        $this->assertInstanceOf(EmptyReadableResponse::class, $result);
    }

    public function test_pingResponseReturned()
    {
        $clientMock = new ClientMock();
        $clientMock->returnSpecificBrokerData(['0AA=']);

        $pingResponse = $this->subscribe->checkForEvent($clientMock);
        $this->assertInstanceOf(PingResp::class, $pingResponse);
    }

    public function test_createPayload()
    {
        $this->subscribe->addTopics(new Topic('test'));
        $this->assertSame('AAR0ZXN0Ag==', base64_encode($this->subscribe->createPayload()));
    }

    public function test_createVariableHeaderWithFixedPacketIdentifier()
    {
        $this->subscribe->setPacketIdentifier(new PacketIdentifier(155));
        $this->assertSame('AJs=', base64_encode($this->subscribe->createVariableHeader()));
    }

    public function test_createVariableHeaderWithRandomPacketIdentifier()
    {
        $output = $this->subscribe->createVariableHeader();
        $humanOutput = DebugTools::convertToBinaryRepresentation($output);
        $this->assertNotSame('0000000000000000', $humanOutput);
    }

    /**
     * @throws \ReflectionException
     */
    public function test_itIsPingTime()
    {
        $clientMock = new ClientMock();
        $clientMock->setPingTime(true);

        $method = new \ReflectionMethod(Subscribe::class, 'checkPingTime');
        $method->setAccessible(true);

        $result = $method->invoke($this->subscribe, $clientMock);
        $this->assertSame(PingReq::class, $clientMock->processObjectWasCalledWithObjectType());
        $this->assertTrue($result);
    }

    public function test_emptyLoopAndBreakOutOfLoop()
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

    public function test_callableFunctionExecutesBeforeLoop()
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
