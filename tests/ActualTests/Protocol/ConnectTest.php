<?php

declare(strict_types=1);

namespace tests\unreal4u\MQTT;

use PHPUnit\Framework\TestCase;
use unreal4u\MQTT\Protocol\Connect;
use unreal4u\MQTT\Protocol\Connect\Parameters;

class ConnectTest extends TestCase
{
    public function test_createVariableHeaderDefaultValues()
    {
        $connect = new Connect(new Parameters('localhost', 'UnitTestClientId'));
        $connectVariableHeader = $connect->createVariableHeader();
        $this->assertSame(10, mb_strlen($connectVariableHeader));
        $this->assertSame('00044d5154540400003c', bin2hex($connectVariableHeader));
    }
}
