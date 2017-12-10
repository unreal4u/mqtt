<?php

declare(strict_types = 1);

use unreal4u\MQTT\Application\Message;
use unreal4u\MQTT\Client;
use unreal4u\MQTT\Protocol\Connect;
use unreal4u\MQTT\Protocol\Connect\Parameters;
use unreal4u\MQTT\Protocol\Publish;

include __DIR__ . '/00.basics.php';

$connectionParameters = new Parameters('publishSomething');
$connectionParameters->setUsername('testuser');
$connectionParameters->setPassword('userpass');

$connect = new Connect();
$connect->setConnectionParameters($connectionParameters);

$client = new Client();
$client->sendData($connect);

define('MAXIMUM', 10);
if ($client->isConnected()) {
    $message = new Message();
    $publish = new Publish();
    $topics = [0 => COMMON_TOPICNAME, 1 => SECONDARY_TOPICNAME];

    for ($i = 1; $i <= MAXIMUM; $i++) {
        $topicId = random_int(0, 1);
        $message->setTopicName($topics[$topicId]);
        $message->setPayload(sprintf('Hello world!! (%d / %d)', $i, MAXIMUM));
        #$message->setQoSLevel(1);
        $publish->setMessage($message);
        $client->sendData($publish);
        echo '.';
    }
}
echo PHP_EOL;
