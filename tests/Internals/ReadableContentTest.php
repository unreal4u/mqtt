<?php

declare(strict_types=1);

namespace tests\unreal4u\MQTT\Internals;

use PHPUnit\Framework\TestCase;
use tests\unreal4u\MQTT\Mocks\ClientMock;
use unreal4u\MQTT\Exceptions\InvalidResponseType;
use unreal4u\MQTT\Internals\ClientInterface;
use unreal4u\MQTT\Internals\ReadableContent;
use unreal4u\MQTT\Internals\ReadableContentInterface;
use unreal4u\MQTT\Protocol\PingResp;
use function chr;

class ReadableContentTest extends TestCase
{
    use ReadableContent;

    public function test_incorrectControlPacketValue()
    {
        $success = chr(100) . chr(0);
        $pingResp = new PingResp();

        $this->expectException(InvalidResponseType::class);
        $pingResp->instantiateObject($success, new ClientMock());
    }

    public function provider_calculateSizeOfRemainingLengthField(): array
    {
        $mapValues[] = [chr(50), chr(50)];
        $mapValues[] = [chr(240), chr(240)];
        $mapValues[] = [chr(1024), chr(1024)];

        return $mapValues;
    }

    /**
     * @dataProvider provider_calculateSizeOfRemainingLengthField
     * @param string $binaryText
     * @param string $numericRepresentation
     */
    public function test_calculateSizeOfRemainingLengthField(string $binaryText, string $numericRepresentation)
    {
        $returnValue = $this->calculateSizeOfRemainingLengthField($binaryText, new ClientMock());
        $this->assertSame($numericRepresentation, $returnValue);
    }

    /**
     * All classes must implement how to handle the object filling
     * @param string $rawMQTTHeaders
     * @param ClientInterface $client
     * @return ReadableContentInterface
     */
    public function fillObject(string $rawMQTTHeaders, ClientInterface $client): ReadableContentInterface
    {
        // Not needed, can be safely ignored
    }
}
