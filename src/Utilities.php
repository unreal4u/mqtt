<?php

declare(strict_types=1);

namespace unreal4u\MQTT;

use LogicException;
use OutOfRangeException;
use unreal4u\MQTT\Exceptions\MessageTooBig;

use function chr;
use function dechex;
use function hexdec;
use function ord;
use function str_pad;

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
     * @throws OutOfRangeException
     */
    public static function convertEndianness(int $number): int
    {
        if ($number > 65535) {
            throw new OutOfRangeException('This is an INT16 conversion, so the maximum is 65535');
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
     * @throws OutOfRangeException
     */
    public static function convertNumberToBinaryString(int $number): string
    {
        if ($number > 65535) {
            throw new OutOfRangeException('This is an INT16 conversion, so the maximum is 65535');
        }

        return chr($number >> 8) . chr($number & 255);
    }

    /**
     * Converts a binary representation of a number to an actual int
     *
     * @param string $binaryString
     * @return int
     * @throws OutOfRangeException
     */
    public static function convertBinaryStringToNumber(string $binaryString): int
    {
        return self::convertEndianness((ord($binaryString{1}) << 8) + (ord($binaryString{0}) & 255));
    }

    /**
     * Returns the correct format for the length in bytes of the remaining bytes
     *
     * Original pseudo-code algorithm as per the documentation:
     * <pre>
     * do
     *     encodedByte = X MOD 128
     *     X = X DIV 128
     *     // if there are more data to encode, set the top bit of this byte
     *     if ( X > 0 )
     *         encodedByte = encodedByte OR 128
     *     endif
     *     'output' encodedByte
     * while ( X > 0 )
     * </pre>
     * @see http://docs.oasis-open.org/mqtt/mqtt/v3.1.1/errata01/os/mqtt-v3.1.1-errata01-os-complete.html#_Toc385349213
     *
     * @param int $lengthInBytes
     * @return string
     * @throws MessageTooBig
     */
    public static function formatRemainingLengthOutput(int $lengthInBytes): string
    {
        if ($lengthInBytes > 268435455) {
            throw new MessageTooBig('The message cannot exceed 268435455 bytes in length');
        }

        $x = $lengthInBytes;
        $outputString = '';
        do {
            $encodedByte = $x % 128;
            $x >>= 7; // Shift 7 bytes
            // if there are more data to encode, set the top bit of this byte
            if ($x > 0) {
                $encodedByte |= 128;
            }
            $outputString .= chr($encodedByte);
        } while ($x > 0);

        return $outputString;
    }

    /**
     * The remaining length of a message is encoded in this specific way, the opposite of formatRemainingLengthOutput
     *
     * Many thanks to Peter's blog for your excellent examples and knowledge.
     * @see http://indigoo.com/petersblog/?p=263
     *
     * Original pseudo-algorithm as per the documentation:
     * <pre>
     * multiplier = 1
     * value = 0
     * do
     *     encodedByte = 'next byte from stream'
     *     value += (encodedByte & 127) * multiplier
     *     if (multiplier > 128*128*128)
     *         throw Error(Malformed Remaining Length)
     *     multiplier *= 128
     * while ((encodedByte & 128) != 0)
     * </pre>
     *
     * @see http://docs.oasis-open.org/mqtt/mqtt/v3.1.1/errata01/os/mqtt-v3.1.1-errata01-os-complete.html#_Toc385349213
     * @see Utilities::formatRemainingLengthOutput()
     *
     * @param string $remainingLengthField
     * @return int
     */
    public static function convertRemainingLengthStringToInt(string $remainingLengthField): int
    {
        $multiplier = 128;
        $value = 0;
        $iteration = 0;

        do {
            // Extract the next byte in the sequence
            $encodedByte = ord($remainingLengthField{$iteration});

            // Add the current multiplier^iteration * first half of byte
            $value += ($encodedByte & 127) * ($multiplier ** $iteration);
            if ($multiplier > 128 ** 3) {
                throw new LogicException('Malformed remaining length field');
            }

            // Prepare for the next iteration
            $iteration++;
        } while (($encodedByte & 128) !== 0);

        return (int)$value;
    }
}
