<?php

/**
 * This package is able to cope with UTF-8 and the MQTT broker without any problems, here are some examples
 */
declare(strict_types=1);

use Monolog\Handler\StreamHandler;
use Monolog\Logger;
use unreal4u\MQTT\Application\Message;
use unreal4u\MQTT\Application\Topic;
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
$connect->setConnectionParameters(new Parameters('publishSomethingUTF8'));

// Create the client connection and connect to the broker
$client = new Client();
$client->sendData($connect);

// Initialize the objects we'll be using for this example
$message = new Message();
$publish = new Publish($logger);

// Set the topic and the payload
$message
    ->setTopic(new Topic(COMMON_TOPICNAME))
    // Below kanjis are 3 bytes long, combined with "normal" 1 byte characters
    ->setPayload('汉A字BC')
    // Example of a veeeeery long message with multibyte (4) UTF-8 characters
    #->setPayload(str_repeat('𠜎', 65534))
;
// Setting the message and publishing to broker
$publish->setMessage($message);
$client->sendData($publish);
echo PHP_EOL;
