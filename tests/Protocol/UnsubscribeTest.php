<?php

declare(strict_types=1);

namespace tests\unreal4u\MQTT;

use PHPUnit\Framework\TestCase;
use unreal4u\MQTT\DataTypes\PacketIdentifier;
use unreal4u\MQTT\DataTypes\Topic;
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

    public function test_shouldExpectAnswer()
    {
        $this->assertTrue($this->unsubscribe->shouldExpectAnswer());
    }

    public function test_addOneTopic()
    {
        $this->unsubscribe->addTopics(new Topic('test'));
        $result = $this->unsubscribe->createPayload();
        $this->assertSame('AAR0ZXN0', base64_encode($result));
    }

    public function test_addMultipleTopicsInOneGo()
    {
        $this->unsubscribe->addTopics(new Topic('test'), new Topic('test2'));
        $result = $this->unsubscribe->createPayload();
        $this->assertSame('AAR0ZXN0AAV0ZXN0Mg==', base64_encode($result));
    }

    public function test_addMultipleTopicsInSteps()
    {
        // Add one topic first
        $this->unsubscribe->addTopics(new Topic('test2'));
        $result = $this->unsubscribe->createPayload();
        $this->assertSame('AAV0ZXN0Mg==', base64_encode($result));

        // Then add another topic
        $this->unsubscribe->addTopics(new Topic('test'));
        $result = $this->unsubscribe->createPayload();
        $this->assertSame('AAV0ZXN0MgAEdGVzdA==', base64_encode($result));

        // One last iteration: add another topic
        $this->unsubscribe->addTopics(new Topic('test3'));
        $result = $this->unsubscribe->createPayload();
        $this->assertSame('AAV0ZXN0MgAEdGVzdAAFdGVzdDM=', base64_encode($result));
    }

    /**
     * @depends test_addOneTopic
     */
    public function test_createVariableHeaderWithStaticPacketIdentifier()
    {
        $this->unsubscribe->addTopics(new Topic('test'));
        $this->unsubscribe->setPacketIdentifier(new PacketIdentifier(8879));
        $packetIdentifier = base64_encode($this->unsubscribe->createVariableHeader());
        $this->assertSame('Iq8=', $packetIdentifier);
    }

    /**
     * @depends test_addOneTopic
     */
    public function test_createVariableHeaderWithRandomPacketIdentifier()
    {
        $this->unsubscribe->addTopics(new Topic('test'));
        $packetIdentifier = DebugTools::convertToBinaryRepresentation($this->unsubscribe->createVariableHeader());
        $this->assertNotSame('0000000000000000', DebugTools::convertToBinaryRepresentation($packetIdentifier));
    }
}
