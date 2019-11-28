<?php

/**
 * Nothing special in this file, just some common settings for each of the examples
 *
 * It is safe to ignore this file. Go check on a1-connectToBroker.php for better examples of how this library works!
 * @see examples/a1-connectToBroker.php
 */

// Set strict typing to true
declare(strict_types = 1);

// Go one directory up
chdir(__DIR__ . '/../');

// include composer's autoloader
include __DIR__.'/../vendor/autoload.php';

// Enable error reporting
error_reporting(E_ALL);
ini_set('display_errors', '1');

// For all our tests, we will define 2 topics on which to execute them
const COMMON_TOPICNAME = 'firstTest';
const SECONDARY_TOPICNAME = 'sensors/baseroom';
const BROKER_HOST = 'mosquitto';
