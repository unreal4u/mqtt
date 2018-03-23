<?php

declare(strict_types=1);

namespace tests\unreal4u\MQTT;

use PHPUnit\Framework\TestCase;
use unreal4u\MQTT\DataTypes\Topic;
use unreal4u\MQTT\Exceptions\MustContainTopic;
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

    public function test_missingTopic()
    {
        $this->expectException(MustContainTopic::class);
        $this->unsubscribe->createVariableHeader();
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

    public function test_addSameTopicMultipleTimes()
    {
        $this->unsubscribe->addTopics(new Topic('test'));
        $result = $this->unsubscribe->createPayload();
        $this->assertSame('AAR0ZXN0', base64_encode($result));

        // Maybe an exception should be thrown?
        $this->markTestIncomplete('Decide whether this should throw an exception');
    }
}
