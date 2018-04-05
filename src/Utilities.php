<?php

declare(strict_types=1);

namespace unreal4u\MQTT;

/**
 * Functionality that is shared across the entire package
 * @package unreal4u\MQTT
 */
final class Utilities
{
    /**
     * Swaps the given number from endian format (INT16, not to be confused with UINT16)
     *
     * @param int $number
     * @return int
     * @throws \OutOfRangeException
     */
    public static function convertEndianness(int $number): int
    {
        if ($number > 65535) {
            throw new \OutOfRangeException('This is an INT16 conversion, so the maximum is 65535');
        }

        $finalNumber = hexdec(
            // Invert first byte and make it a complete hexadecimal number
            str_pad(dechex($number & 255), 2, '0', STR_PAD_LEFT) .
            // Invert second byte and make a complete hexadecimal number
            str_pad(dechex($number >> 8), 2, '0', STR_PAD_LEFT)
        );

        return (int)$finalNumber;
    }

    /**
     * Converts a number to a binary string that the MQTT protocol understands
     *
     * @param int $number
     * @return string
     * @throws \OutOfRangeException
     */
    public static function convertNumberToBinaryString(int $number): string
    {
        if ($number > 65535) {
            throw new \OutOfRangeException('This is an INT16 conversion, so the maximum is 65535');
        }

        return \chr($number >> 8) . \chr($number & 255);
    }

    /**
     * Converts a binary representation of a number to an actual int
     *
     * @param string $binaryString
     * @return int
     * @throws \OutOfRangeException
     */
    public static function convertBinaryStringToNumber(string $binaryString): int
    {
        return self::convertEndianness((\ord($binaryString{1}) << 8) + (\ord($binaryString{0}) & 255));
    }
}
