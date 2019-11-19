<?php

declare(strict_types=1);

namespace tests\unreal4u\MQTT\DataTypes;

use PHPUnit\Framework\TestCase;
use unreal4u\MQTT\DataTypes\QoSLevel;
use unreal4u\MQTT\Exceptions\InvalidQoSLevel;

class QoSLevelTest extends TestCase
{
    public function testInvalidQoSLevel(): void
    {
        $this->expectException(InvalidQoSLevel::class);
        new QoSLevel(-1);
    }

    public function providerValidQoSLevels(): array
    {
        $mapValues[] = [0];
        $mapValues[] = [1];
        $mapValues[] = [2];

        return $mapValues;
    }

    /**
     * @dataProvider providerValidQoSLevels
     * @param int $level
     */
    public function testValidQoSLevels(int $level): void
    {
        $QoSLevel = new QoSLevel($level);
        $this->assertSame($level, $QoSLevel->getQoSLevel());
    }

    /**
     * @dataProvider providerValidQoSLevels
     * @param int $level
     */
    public function testToString(int $level): void
    {
        $QoSLevel = new QoSLevel($level);
        $this->assertSame((string)$level, (string)$QoSLevel);
    }
}
