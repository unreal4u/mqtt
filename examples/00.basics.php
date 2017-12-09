<?php

/*
 * Nothing special in this file, just some common settings for each of the examples
 */

declare(strict_types = 1);
chdir(__DIR__ . '/../');

include __DIR__.'/../vendor/autoload.php';

error_reporting(E_ALL);
ini_set('display_errors', '1');

const COMMON_TOPICNAME = 'firstTest';
const SECONDARY_TOPICNAME = 'secondaryTest';

function str2bin($str)
{
    $out=null;
    $strLength = \strlen($str);
    for($a=0; $a < $strLength; $a++) {
        $dec = \ord(substr($str,$a,1)); //determine symbol ASCII-code
        $bin = sprintf('%08d', base_convert($dec, 10, 2)); //convert to binary representation and add leading zeros
        $out .= $bin;
    }
    return $out;
}
