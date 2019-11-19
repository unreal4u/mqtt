<?php

declare(strict_types=1);

namespace tests\unreal4u\MQTT;

use PHPUnit\Framework\TestCase;
use unreal4u\MQTT\DataTypes\PacketIdentifier;
use unreal4u\MQTT\DataTypes\TopicFilter;
use unreal4u\MQTT\DebugTools;
use unreal4u\MQTT\Protocol\Unsubscribe;

class UnsubscribeTest extends TestCase
{
    /**
     * @var Unsubscribe
     */
    private $unsubscribe;

    protected function setUp()
    {
        $this->unsubscribe = new Unsubscribe();
        parent::setUp();
    }

    public function testShouldExpectAnswer(): void
    {
        $this->assertTrue($this->unsubscribe->shouldExpectAnswer());
    }

    public function testAddOneTopic(): void
    {
        $this->unsubscribe->addTopics(new TopicFilter('test'));
        $result = $this->unsubscribe->createPayload();
        $this->assertSame('AAR0ZXN0', base64_encode($result));
    }

    public function testAddMultipleTopicsInOneGo(): void
    {
        $this->unsubscribe->addTopics(new TopicFilter('test'), new TopicFilter('test2'));
        $result = $this->unsubscribe->createPayload();
        $this->assertSame('AAR0ZXN0AAV0ZXN0Mg==', base64_encode($result));
    }

    public function testAddMultipleTopicsInSteps(): void
    {
        // Add one topic first
        $this->unsubscribe->addTopics(new TopicFilter('test2'));
        $result = $this->unsubscribe->createPayload();
        $this->assertSame('AAV0ZXN0Mg==', base64_encode($result));

        // Then add another topic
        $this->unsubscribe->addTopics(new TopicFilter('test'));
        $result = $this->unsubscribe->createPayload();
        $this->assertSame('AAV0ZXN0MgAEdGVzdA==', base64_encode($result));

        // One last iteration: add another topic
        $this->unsubscribe->addTopics(new TopicFilter('test3'));
        $result = $this->unsubscribe->createPayload();
        $this->assertSame('AAV0ZXN0MgAEdGVzdAAFdGVzdDM=', base64_encode($result));
    }

    /**
     * @depends testAddOneTopic
     */
    public function testCreateVariableHeaderWithStaticPacketIdentifier(): void
    {
        $this->unsubscribe->addTopics(new TopicFilter('test'));
        $this->unsubscribe->setPacketIdentifier(new PacketIdentifier(8879));
        $packetIdentifier = base64_encode($this->unsubscribe->createVariableHeader());
        $this->assertSame('Iq8=', $packetIdentifier);
    }

    /**
     * @depends testAddOneTopic
     */
    public function testCreateVariableHeaderWithRandomPacketIdentifier(): void
    {
        $this->unsubscribe->addTopics(new TopicFilter('test'));
        $packetIdentifier = DebugTools::convertToBinaryRepresentation($this->unsubscribe->createVariableHeader());
        $this->assertNotSame('0000000000000000', DebugTools::convertToBinaryRepresentation($packetIdentifier));
    }
}
