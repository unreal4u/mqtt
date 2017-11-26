<?php

declare(strict_types=1);

namespace tests\unreal4u\MQTT;

use PHPUnit\Framework\TestCase;
use tests\unreal4u\MQTT\Mocks\ReadableBaseMock;
use unreal4u\MQTT\Exceptions\InvalidResponseType;

class ReadableBaseTest extends TestCase
{
    /**
     * @var ReadableBaseMock
     */
    protected $readableBase;

    public function test_checkInvalidControlPacketValueThrowsException()
    {
        // Check for invalid responses
        $this->expectException(InvalidResponseType::class);
        $this->readableBase = new ReadableBaseMock(\chr(208) . \chr(0));
    }
}
