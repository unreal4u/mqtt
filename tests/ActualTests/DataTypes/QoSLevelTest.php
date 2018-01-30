<?php

declare(strict_types=1);

namespace tests\unreal4u\MQTT\Application;

use PHPUnit\Framework\TestCase;
use unreal4u\MQTT\DataTypes\QoSLevel;
use unreal4u\MQTT\Exceptions\InvalidQoSLevel;

class QoSLevelTest extends TestCase
{
    public function test_invalidQoSLevel()
    {
        $this->expectException(InvalidQoSLevel::class);
        new QoSLevel(-1);
    }

    public function provider_validQoSLevels(): array
    {
        $mapValues[] = [0];
        $mapValues[] = [1];
        $mapValues[] = [2];

        return $mapValues;
    }

    /**
     * @dataProvider provider_validQoSLevels
     * @param int $level
     */
    public function test_validQoSLevels(int $level)
    {
        $QoSLevel = new QoSLevel($level);
        $this->assertSame($level, $QoSLevel->getQoSLevel());
    }
}
