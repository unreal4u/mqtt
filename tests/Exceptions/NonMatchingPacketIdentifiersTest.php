<?php

declare(strict_types=1);

namespace tests\unreal4u\MQTT\Exceptions;

use PHPUnit\Framework\TestCase;
use unreal4u\MQTT\DataTypes\PacketIdentifier;
use unreal4u\MQTT\Exceptions\NonMatchingPacketIdentifiers;

class NonMatchingPacketIdentifiersTest extends TestCase
{
    /**
     * @var NonMatchingPacketIdentifiers
     */
    private $exception;

    protected function setUp()
    {
        $this->exception = new NonMatchingPacketIdentifiers();
        parent::setUp();
    }

    public function testOriginPacketIdentifierIsSetAndReturnedCorrectly(): void
    {
        $this->exception->setOriginPacketIdentifier(new PacketIdentifier(908));
        $this->assertSame(908, $this->exception->getOriginPacketIdentifierValue());
    }

    public function testReturnedPacketIdentifierIsSetAndReturnedCorrectly(): void
    {
        $this->exception->setReturnedPacketIdentifier(new PacketIdentifier(909));
        $this->assertSame(909, $this->exception->getReturnedPacketIdentifierValue());
    }
}
