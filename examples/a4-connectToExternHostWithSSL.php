<?php

/**
 * This example will connect to the broker through SSL
 */

declare(strict_types=1);

use unreal4u\MQTT\DataTypes\BrokerPort;
use unreal4u\MQTT\DataTypes\ClientId;
use unreal4u\MQTT\Client;
use unreal4u\MQTT\DataTypes\Message;
use unreal4u\MQTT\DataTypes\QoSLevel;
use unreal4u\MQTT\DataTypes\TopicName;
use unreal4u\MQTT\Protocol\Connect;
use unreal4u\MQTT\Protocol\Connect\Parameters;
use unreal4u\MQTT\Protocol\Publish;

include __DIR__ . '/00.basics.php';

$parameters = new Parameters(new ClientId(basename(__FILE__)), 'mqtt.example.com');
$parameters->setBrokerPort(new BrokerPort(8883, 'ssl'));

$connect = new Connect();
$connect->setConnectionParameters($parameters);

// Create a client connection
$client = new Client();
// And send the data
try {
    /** @var \unreal4u\MQTT\Protocol\ConnAck $connAck */
    $client->processObject($connect);
    /*
     * Proceed only if we connected successfully, if we provide the incorrect parameters above we have no way of knowing
     * whether we could connect with the chosen protocol unless we pass through ConnAck.
     */
    if ($client->isConnected()) {
        echo 'We are connected successfully to the broker' . PHP_EOL;
        $publish = new Publish();
        $message = new Message('Hello world through a secure connection!', new TopicName(COMMON_TOPICNAME));
        $message->setQoSLevel(new QoSLevel(1));
        $publish->setMessage($message);
        $pubAck = $client->processObject($publish);
        var_dump($pubAck);
    } else {
        echo 'We are NOT connected and we can NOT send a message! ' . PHP_EOL;
    }
} catch (\Exception $e) {
    // We couldn't even connect, so die early
    var_dump($e);
}
