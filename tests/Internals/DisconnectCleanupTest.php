<?php

declare(strict_types=1);

namespace tests\unreal4u\MQTT\Internals;

use PHPUnit\Framework\TestCase;
use tests\unreal4u\MQTT\Mocks\ClientMock;
use unreal4u\MQTT\Internals\DisconnectCleanup;
use unreal4u\MQTT\Protocol\Disconnect;

class DisconnectCleanupTest extends TestCase
{
    /**
     * @var DisconnectCleanup
     */
    private $disconnectCleanup;

    protected function setUp()
    {
        parent::setUp();
        $this->disconnectCleanup = new DisconnectCleanup();
    }

    public function testGetOriginPacketControl(): void
    {
        $this->assertSame(0, $this->disconnectCleanup->getOriginControlPacket());
    }

    public function testObjectIsCreatedSuccessfully(): void
    {
        $this->assertInstanceOf(DisconnectCleanup::class, $this->disconnectCleanup->fillObject('', new ClientMock()));
    }

    public function testPerformSpecialActions(): void
    {
        $clientMock = new ClientMock();

        $this->disconnectCleanup->performSpecialActions($clientMock, new Disconnect());
        $this->assertTrue($clientMock->shutdownConnectionWasCalled());
        $this->assertTrue($clientMock->setConnectedWasCalled());
    }
}
