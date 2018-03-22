<?php

/**
 * This package is able to cope with UTF-8 and the MQTT broker without any problems, here are some examples
 */
declare(strict_types=1);

use Monolog\Handler\StreamHandler;
use Monolog\Logger;
use unreal4u\MQTT\DataTypes\ClientId;
use unreal4u\MQTT\DataTypes\Message;
use unreal4u\MQTT\DataTypes\Topic;
use unreal4u\MQTT\Client;
use unreal4u\MQTT\Protocol\Connect;
use unreal4u\MQTT\Protocol\Connect\Parameters;
use unreal4u\MQTT\Protocol\Publish;

include __DIR__ . '/00.basics.php';

// Instantiate a logger to know exactly what we are doing
$logger = new Logger('main');
$logger->pushHandler(new StreamHandler('php://stdout', Logger::DEBUG));

// Create the Connect object and set the parameters
$connect = new Connect();
$connect->setConnectionParameters(new Parameters(new ClientId(basename(__FILE__))));

// Create the client connection and connect to the broker
$client = new Client();
$client->processObject($connect);

// Initialize the objects we'll be using for this example
// Below kanjis are 3 bytes long, combined with "normal" 1 byte characters
// Example of a veeeeery long message with multibyte (4) UTF-8 characters
#str_repeat('𠜎', 65534)
$message = new Message('汉A字BC', new Topic(COMMON_TOPICNAME));
$publish = new Publish($logger);

// Setting the message and publishing to broker
$publish->setMessage($message);
$client->processObject($publish);
echo PHP_EOL;
