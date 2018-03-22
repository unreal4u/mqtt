<?php

declare(strict_types=1);

namespace tests\unreal4u\MQTT;

use PHPUnit\Framework\TestCase;
use unreal4u\MQTT\DebugTools;

/**
 * Just some basic testing, to ensure stuff is actually working, no need for extensive testing of edge-cases
 *
 * Class DebugToolsTest
 * @package tests\unreal4u\MQTT
 */
class DebugToolsTest extends TestCase
{
    public function test_convertToBinaryRepresentation()
    {
        $binaryString = base64_decode('cAIAJg==');
        $result = DebugTools::convertToBinaryRepresentation($binaryString);

        $this->assertSame('01110000000000100000000000100110', $result);
    }

    public function test_convertBinaryToBase64String()
    {
        $binaryString = '01110000000000100000000000100110';
        $result = DebugTools::convertBinaryToString($binaryString, true);

        $this->assertSame('cAIAJg==', $result);
    }

    public function test_convertBinaryToString()
    {
        $binaryString = '0111010101101110011100100110010101100001011011000011010001110101';
        $result = DebugTools::convertBinaryToString($binaryString);

        $this->assertSame('unreal4u', $result);
    }
}
