<?php

declare(strict_types=1);

namespace tests\unreal4u\MQTT;

use PHPUnit\Framework\TestCase;
use tests\unreal4u\MQTT\Mocks\ClientMock;
use unreal4u\MQTT\Application\EmptyReadableResponse;
use unreal4u\MQTT\DataTypes\PacketIdentifier;
use unreal4u\MQTT\DataTypes\Topic;
use unreal4u\MQTT\DebugTools;
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
        $clientMock->returnSpecificBrokerData(base64_decode('0AA='));

        $pingResponse = $this->subscribe->checkForEvent($clientMock);
        $this->assertInstanceOf(PingResp::class, $pingResponse);
    }

    public function test_addOneTopic()
    {
        $this->subscribe->addTopics(new Topic('test'));
        $this->assertSame(1, $this->subscribe->getNumberOfTopics());
    }

    public function test_addMultipleTopicsInOneGo()
    {
        $this->subscribe->addTopics(new Topic('test_a'), new Topic('test_b'));
        $this->assertSame(2, $this->subscribe->getNumberOfTopics());
    }

    public function test_addMultipleTopicsInMultipleStages()
    {
        $this->subscribe->addTopics(new Topic('test_a'));
        $this->assertSame(1, $this->subscribe->getNumberOfTopics());

        $this->subscribe->addTopics(new Topic('test_b'));
        $this->assertSame(2, $this->subscribe->getNumberOfTopics());
    }

    public function test_addSameTopicMoreThanOnce()
    {
        $this->subscribe->addTopics(new Topic('test_a'));
        $this->assertSame(1, $this->subscribe->getNumberOfTopics());

        $this->subscribe->addTopics(new Topic('test_a'));
        $this->markTestIncomplete('Not implemented yet');
        $this->assertSame(1, $this->subscribe->getNumberOfTopics());

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
}
