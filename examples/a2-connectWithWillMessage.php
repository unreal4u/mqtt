<?php

/**
 * This example will connect to the broker setting a will message while doing so
 */

declare(strict_types=1);

use unreal4u\MQTT\Client;
use unreal4u\MQTT\DataTypes\ClientId;
use unreal4u\MQTT\DataTypes\Message;
use unreal4u\MQTT\DataTypes\TopicName;
use unreal4u\MQTT\Protocol\Connect;
use unreal4u\MQTT\Protocol\Connect\Parameters;

include __DIR__ . '/00.basics.php';

$clientId = new ClientId(basename(__FILE__));

// Create a new Message object
$willMessage = new Message(
    sprintf('If I die unexpectedly, please print this message. Used ClientId: %s', $clientId->getClientId()),
    new TopicName('client/errors')
);

// Now we will setup a new Connect Parameters object
$parameters = new Parameters($clientId, BROKER_HOST);
// Set the will message to the above created message
$parameters->setWill($willMessage);

// Example of invalid protocol which will throw an exception:
#$parameters->setProtocolVersion(new unreal4u\MQTT\DataTypes\ProtocolVersion('0.0.1'));
// Setup the connection
$connect = new Connect();
// And set the parameters
$connect->setConnectionParameters($parameters);

// Create a client connection
$client = new Client();
// And send the data
try {
    /** @var \unreal4u\MQTT\Protocol\ConnAck $connAck */
    $connAck = $client->processObject($connect);
} catch (\Exception $e) {
    // We couldn't even connect, so die early
    var_dump($e);
    die();
}

/*
 * If you subscribe to the above topic, a will message will be set when this exception below is thrown
 *
 * Example of subscription:
 * mosquitto_sub -v -t client/errors
 *
 * Where:
 * -t is topic
 * -v is verbose (will print out the topic before the message itself)
 */
for ($i = 0; $i < 3; $i++) {
    sleep(1);
    if ($i === 1) {
        throw new \LogicException('Throwing an exception unexpectedly will not trigger the destructor');
    }
}
