<?php

/**
 * This example will connect to the broker setting a will message while doing so
 */
declare(strict_types = 1);

use unreal4u\MQTT\DataTypes\ClientId;
use unreal4u\MQTT\DataTypes\Topic;
use unreal4u\MQTT\Client;
use unreal4u\MQTT\Protocol\Connect;
use unreal4u\MQTT\Protocol\Connect\Parameters;
use unreal4u\MQTT\Application\Message;

include __DIR__ . '/00.basics.php';

// Create a new Message object
$willMessage = new Message();
// Set the payload
$willMessage->setPayload('If I die unexpectedly, please print this message');
// And set the topic
$willMessage->setTopic(new Topic('client/errors'));

// Now we will setup a new Connect Parameters object
$parameters = new Parameters(new ClientId(basename(__FILE__)));
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
    if ($i === 2) {
        throw new \LogicException('Throwing an exception unexpectedly will not trigger the destructor');
    }
}
