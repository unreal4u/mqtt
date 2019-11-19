<?php

declare(strict_types=1);

namespace tests\unreal4u\MQTT;

use PHPUnit\Framework\TestCase;
use tests\unreal4u\MQTT\Mocks\ClientMock;
use unreal4u\MQTT\Internals\DisconnectCleanup;
use unreal4u\MQTT\Protocol\Disconnect;

class DisconnectTest extends TestCase
{
    /**
     * @var Disconnect
     */
    private $disconnect;

    protected function setUp()
    {
        parent::setUp();
        $this->disconnect = new Disconnect();
    }

    protected function tearDown()
    {
        parent::tearDown();
        $this->disconnect = null;
    }

    public function testCreateVariableHeader(): void
    {
        $this->assertSame('', $this->disconnect->createVariableHeader());
    }

    public function testCreatePayload(): void
    {
        $this->assertSame('', $this->disconnect->createPayload());
    }

    public function testExpectAnswer(): void
    {
        $this->assertInstanceOf(DisconnectCleanup::class, $this->disconnect->expectAnswer('0', new ClientMock()));
    }

    public function testShouldExpectAnswer(): void
    {
        $this->assertFalse($this->disconnect->shouldExpectAnswer());
    }
}
