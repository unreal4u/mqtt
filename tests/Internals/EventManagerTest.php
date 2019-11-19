<?php

declare(strict_types=1);

namespace tests\unreal4u\MQTT\Internals;

use PHPUnit\Framework\TestCase;
use tests\unreal4u\MQTT\Mocks\ClientMock;
use unreal4u\MQTT\Application\EmptyReadableResponse;
use unreal4u\MQTT\Internals\EventManager;

class EventManagerTest extends TestCase
{
    /**
     * @var EventManager
     */
    private $eventManager;

    protected function setUp()
    {
        $this->eventManager = new EventManager();
        parent::setUp();
    }

    protected function tearDown()
    {
        parent::tearDown(); // TODO: Change the autogenerated stub
        $this->eventManager = null;
    }

    public function test_emptyRawHeaders()
    {
        $this->assertInstanceOf(
            EmptyReadableResponse::class,
            $this->eventManager->analyzeHeaders('', new ClientMock())
        );
    }

    public function test_invalidControlPacketValue()
    {
        $this->expectException(\DomainException::class);
        $this->eventManager->analyzeHeaders(
            base64_decode('ECwABE1RVFQEBAA8ABBVbml0VGVzdENsaWVudElkAAV0b3BpYwAHVGVzdGluZw=='),
            new ClientMock()
        );
    }
}
