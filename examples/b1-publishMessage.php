<?php

/**
 * This example will connect to the broker and send 10 messages to two different topics by both of them randomly.
 */

declare(strict_types=1);

use unreal4u\MQTT\Client;
use unreal4u\MQTT\DataTypes\ClientId;
use unreal4u\MQTT\DataTypes\Message;
use unreal4u\MQTT\DataTypes\TopicName;
use unreal4u\MQTT\Protocol\Connect;
use unreal4u\MQTT\Protocol\Connect\Parameters;
use unreal4u\MQTT\Protocol\Publish;

include __DIR__ . '/00.basics.php';

// For this connection, we'll do something different: we'll connect with a username and password. This is done by doing:
$connectionParameters = new Parameters(new ClientId(basename(__FILE__)), BROKER_HOST);
$connectionParameters->setCredentials('testuser', 'userpass');

// Now we'll setup a Connect object
$connect = new Connect();
// And pass on the parameters
$connect->setConnectionParameters($connectionParameters);

// Next, we'll setup a Client
$client = new Client();
// And perform the connection itself
$client->processObject($connect);

// Once we are done with that and we are connected, we'll send out 10 messages
define('MAXIMUM', 10);
if ($client->isConnected()) {
    // We will also set up a Publish object
    $publish = new Publish();
    // And we'll define 2 topics
    $topics = [0 => COMMON_TOPICNAME, 1 => SECONDARY_TOPICNAME];

    for ($i = 1; $i <= MAXIMUM; $i++) {
        // We'll pick a random topic
        $topicId = random_int(0, 1);

        // And we'll set the message to the Publish object
        $publish->setMessage(
            new Message(
                sprintf(
                    'Hello world!! (%d / %d, %s)',
                    $i,
                    MAXIMUM,
                    $topics[$topicId]
                ),
                new TopicName($topics[$topicId])
            )
        );
        // Finally, we are ready to send it to the broker
        $client->processObject($publish);
        // A small indicator that something is happening
        echo '.';
    }
}
echo PHP_EOL;
