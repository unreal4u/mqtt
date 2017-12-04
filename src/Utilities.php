<?php

declare(strict_types=1);

namespace unreal4u\MQTT;

final class Utilities
{
    /**
     * Swaps the given number from endian format (INT16, not to be confused with UINT16)
     *
     * @param int $number
     * @return int
     */
    public static function convertEndianness(int $number): int
    {
        $finalNumber = hexdec(
            // Invert first byte and make it a complete hexadecimal number
            str_pad(dechex($number & 255), 2, '0', STR_PAD_LEFT) .
            // Invert second byte and make a complete hexadecimal number
            str_pad(dechex($number >> 8), 2, '0', STR_PAD_LEFT)
        );

        // Perform INT16 conversion instead of UINT16
        if ($finalNumber > 32537) {
            $finalNumber -= 65536;
        }

        return (int)$finalNumber;
    }

    public static function convertNumberToBinaryString(int $number): string
    {
        $convertedNumber = self::convertEndianness($number);
        return \chr($convertedNumber & 255) . \chr($convertedNumber >> 8);
    }
}
