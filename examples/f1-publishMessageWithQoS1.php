<?php

/**
 * QoS level 1 and 2 refer to the confirmation the sent message is received by the other party, either broker or client
 *
 * In the first case and provided a QoS level 1, if we sent a message to the broker, the broker will respond with a
 * confirmation message (PubAck) and within that, the packetIdentifier which must match the packetIdentifier we sent to
 * the broker during Publish.
 */

declare(strict_types=1);

use Monolog\Handler\StreamHandler;
use Monolog\Logger;
use unreal4u\MQTT\DataTypes\ClientId;
use unreal4u\MQTT\DataTypes\Message;
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
$connectionParameters = new Parameters(new ClientId(basename(__FILE__)));
$connectionParameters->setUsername('testuser');
$connectionParameters->setPassword('userpass');

// And connect, every object will give you output about what it is doing
$connect = new Connect($logger);
$connect->setConnectionParameters($connectionParameters);

// Make the initial connection
$client = new Client($logger);
$client->processObject($connect);

define('MAXIMUM', 3);
if ($client->isConnected()) {
    // Set main topic (equal for all messages)
    $topic = new Topic(COMMON_TOPICNAME);
    // Create a new Publish object
    $publish = new Publish($logger);

    for ($i = 1; $i <= MAXIMUM; $i++) {
        $message = new Message(sprintf('Hello world!! (%d / %d)', $i, MAXIMUM), $topic);
        // Set the message to the Publish object
        $message->setQoSLevel(new QoSLevel(1));
        $publish->setMessage($message);
        // The client will perform the check whether the packet identifier is correctly set or not
        $client->processObject($publish);
        echo '.';
    }
}
echo PHP_EOL;
