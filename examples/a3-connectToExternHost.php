<?php

/**
 * This example will connect to the broker setting a will message while doing so
 */

declare(strict_types=1);

use unreal4u\MQTT\DataTypes\ClientId;
use unreal4u\MQTT\Client;
use unreal4u\MQTT\Protocol\Connect;
use unreal4u\MQTT\Protocol\Connect\Parameters;

include __DIR__ . '/00.basics.php';

$connect = new Connect();
// Set the parameters to connect to the broker located at 192.168.44.55
$connect->setConnectionParameters(new Parameters(new ClientId(basename(__FILE__)), '192.168.44.55'));

// Create a client connection
$client = new Client();
// And send the data
try {
    /** @var \unreal4u\MQTT\Protocol\ConnAck $connAck */
    $connAck = $client->processObject($connect);
} catch (\Exception $e) {
    // We couldn't even connect, so die early
    var_dump($e);
}
