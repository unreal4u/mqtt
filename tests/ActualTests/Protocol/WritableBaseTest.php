<?php

declare(strict_types=1);

namespace tests\unreal4u\MQTT;

use PHPUnit\Framework\TestCase;
use tests\unreal4u\MQTT\Mocks\WritableBaseMock;
use unreal4u\MQTT\Exceptions\MessageTooBig;

class WritableBaseTest extends TestCase
{
    /**
     * @var WritableBaseMock
     */
    protected $writableBase;

    protected function setUp()
    {
        $this->writableBase = new WritableBaseMock();
    }

    protected function tearDown()
    {
        $this->writableBase = null;
    }

    public function provider_getRemainingLength(): array
    {
        $mapValues[] = [126, 1, '7e'];
        $mapValues[] = [127, 1, '7f'];
        $mapValues[] = [128, 2, '8001'];
        $mapValues[] = [16383, 2, 'ff7f'];
        $mapValues[] = [16384, 3, '808001'];
        $mapValues[] = [2097151, 3, 'ffff7f'];
        $mapValues[] = [2097152, 4, '80808001'];
        $mapValues[] = [268435455, 4, 'ffffff7f'];

        return $mapValues;
    }

    /**
     * @dataProvider provider_getRemainingLength
     *
     * @param int $input
     * @param int $length
     * @param string $expected
     */
    public function test_getRemainingLength(int $input, int $length, string $expected)
    {
        $output = $this->writableBase->getRemainingLength($input);

        $this->assertSame($length, mb_strlen($output));
        $humanOutput = '';
        for ($i = 0; $i < $length; $i++) {
            $humanOutput .= sprintf('%02x', \ord($output[$i]));
        }

        $this->assertSame($expected, $humanOutput);
    }

    public function test_OverflowGetRemainingLength()
    {
        $this->expectException(MessageTooBig::class);
        $this->writableBase->getRemainingLength(268435456);
    }

    public function test_validProtocolLevel()
    {
        $output = $this->writableBase->getProtocolLevel();
        $this->assertSame(4, \ord($output));
    }
}
