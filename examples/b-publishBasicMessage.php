<?php

/**
 * This example will connect to the broker and send 10 messages to two different topics by both of them randomly.
 */
declare(strict_types = 1);

use unreal4u\MQTT\Application\Message;
use unreal4u\MQTT\Application\Topic;
use unreal4u\MQTT\Client;
use unreal4u\MQTT\Protocol\Connect;
use unreal4u\MQTT\Protocol\Connect\Parameters;
use unreal4u\MQTT\Protocol\Publish;

include __DIR__ . '/00.basics.php';

// For this connection, we'll do something different: we'll connect with a username and password. This is done by doing:
$connectionParameters = new Parameters('publishSomething');
$connectionParameters->setUsername('testuser');
$connectionParameters->setPassword('userpass');

// Now we'll setup a Connect object
$connect = new Connect();
// And pass on the parameters
$connect->setConnectionParameters($connectionParameters);

// Next, we'll setup a Client
$client = new Client();
// And perform the connection itself
$client->sendData($connect);

// Once we are done with that and we are connected, we'll send out 10 messages
define('MAXIMUM', 10);
if ($client->isConnected()) {
    // So, set up the Message
    $message = new Message();
    // We will also set up a Publish object
    $publish = new Publish();
    // And we'll define 2 topics
    $topics = [0 => COMMON_TOPICNAME, 1 => SECONDARY_TOPICNAME];

    for ($i = 1; $i <= MAXIMUM; $i++) {
        // We'll pick a random topic
        $topicId = random_int(0, 1);
        // And set it to the message
        $message->setTopic(new Topic($topics[$topicId]));
        // Next, we'll setup the payload
        $message->setPayload(sprintf('Hello world!! (%d / %d)', $i, MAXIMUM));

        // And we'll set the message to the Publish object
        $publish->setMessage($message);
        // Finally, we are ready to send it to the broker
        $client->sendData($publish);
        // A small indicator that something is happening
        echo '.';
    }
}
echo PHP_EOL;
