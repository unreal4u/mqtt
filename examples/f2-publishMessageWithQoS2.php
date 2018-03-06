<?php

/**
 * QoS level 1 and 2 refer to the confirmation the sent message is received by the other party, either broker or client
 *
 * In the first case and provided a QoS level 1, if we sent a message to the broker, the broker will respond with a
 * confirmation message (PubAck) and within that, the packetIdentifier which must match the packetIdentifier we sent to
 * the broker during Publish.
 */

declare(strict_types = 1);

use Monolog\Handler\StreamHandler;
use Monolog\Logger;
use unreal4u\MQTT\Application\Message;
use unreal4u\MQTT\DataTypes\Topic;
use unreal4u\MQTT\Client;
use unreal4u\MQTT\DataTypes\QoSLevel;
use unreal4u\MQTT\Protocol\Connect;
use unreal4u\MQTT\Protocol\Connect\Parameters;
use unreal4u\MQTT\Protocol\Publish;

include __DIR__ . '/00.basics.php';

// For this example, we'll set up a Logger as well, using Monolog we'll print everything to the terminal
$logger = new Logger('main');
$logger->pushHandler(new StreamHandler('php://stdout', Logger::DEBUG));

// Set up the connection parameters
$connectionParameters = new Parameters(basename(__FILE__));
$connectionParameters->setUsername('testuser');
$connectionParameters->setPassword('userpass');

// And connect, every object will give you output about what it is doing
$connect = new Connect($logger);
$connect->setConnectionParameters($connectionParameters);

// Make the initial connection
$client = new Client($logger);
$client->processObject($connect);

define('MAXIMUM', 1);
if ($client->isConnected()) {
    // If we are connected, set up a new message
    $message = new Message();
    // Set the topic name
    $message->setTopic(new Topic(COMMON_TOPICNAME));
    // QoS level is set per message, so set it here
    $message->setQoSLevel(new QoSLevel(2));
    // Create a new Publish object
    $publish = new Publish($logger);
    $publish->packetIdentifier = 35;

    for ($i = 1; $i <= MAXIMUM; $i++) {
        // Set the payload
        $message->setPayload(sprintf('Hello world!! (%d / %d)', $i, MAXIMUM));
        // Set the message to the Publish object
        $publish->setMessage($message);
        // The client will perform the check whether the packet identifier is correctly set or not
        $client->processObject($publish);
        echo '.';
    }
}
echo PHP_EOL;
