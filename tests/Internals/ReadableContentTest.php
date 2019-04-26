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
use function base64_decode;
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

    public function provider_performRemainingLengthFieldOperations(): array
    {
        $mapValues[] = [base64_decode('Ag=='), 1]; // remaining length: 2, which is 1 byte long
        $mapValues[] = [base64_decode('yAE='), 2]; // remaining length: 200, which is 2 bytes long
        $mapValues[] = [base64_decode('qcoB'), 3]; // remaining length: 25897, which is 3 bytes long
        $mapValues[] = [base64_decode('////fw=='), 4]; // remaining length: 268435455, which is 4 bytes long

        return $mapValues;
    }

    /**
     * @dataProvider provider_performRemainingLengthFieldOperations
     * @param string $binaryText
     * @param int $numericRepresentation
     */
    public function test_performRemainingLengthFieldOperations(string $binaryText, int $numericRepresentation)
    {
        $clientMock = new ClientMock();
        $returnValue = $this->performRemainingLengthFieldOperations($binaryText, $clientMock);
        $this->assertSame($numericRepresentation, $returnValue);
    }

    public function provider_calculateSizeOfRemainingLengthField(): array
    {
        $mapValues[] = [1, 1];
        $mapValues[] = [2, 1];
        $mapValues[] = [50, 1];
        $mapValues[] = [128, 2];
        $mapValues[] = [1280, 2];
        $mapValues[] = [16400, 3];
        $mapValues[] = [2097150, 3];
        $mapValues[] = [2097152, 4];
        $mapValues[] = [268435455, 4];

        return $mapValues;
    }

    /**
     * @dataProvider provider_calculateSizeOfRemainingLengthField
     * @param int $size
     * @param int $expectedByteSize
     */
    public function test_calculateSizeOfRemainingLengthField(int $size, int $expectedByteSize)
    {
        $returnValue = $this->calculateSizeOfRemainingLengthField($size);
        $this->assertSame($expectedByteSize, $returnValue);
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
