<?php

declare(strict_types = 1);

use Monolog\Handler\StreamHandler;
use Monolog\Logger;
use unreal4u\MQTT\Application\Message;
use unreal4u\MQTT\Application\SimplePayload;
use unreal4u\MQTT\Client;
use unreal4u\MQTT\Protocol\Connect;
use unreal4u\MQTT\Protocol\Connect\Parameters;
use unreal4u\MQTT\Protocol\Publish;

include __DIR__ . '/00.basics.php';

$logger = new Logger('main');
$logger->pushHandler(new StreamHandler('php://stdout', Logger::DEBUG));

$connectionParameters = new Parameters('publishSomething');
$connectionParameters->setUsername('testuser');
$connectionParameters->setPassword('userpass');

$connect = new Connect($logger);
$connect->setConnectionParameters($connectionParameters);

$client = new Client($logger);
$client->sendData($connect);

define('MAXIMUM', 3);
if ($client->isConnected()) {
    $payload = new SimplePayload();
    $message = new Message();
    $message->setTopicName(COMMON_TOPICNAME);
    $message->setQoSLevel(1);
    #$message->setRetainFlag(true);
    $publish = new Publish($logger);

    for ($i = 1; $i <= MAXIMUM; $i++) {
        $payload->setPayload(sprintf('Hello world!! (%d / %d)', $i, MAXIMUM));
        $message->setPayload($payload);
        $publish->setMessage($message);
        $pubAck = $client->sendData($publish);
        if ($pubAck->packetIdentifier === $publish->packetIdentifier) {
            echo '------- OK' . PHP_EOL;
        }
        echo '.';
    }
}
echo PHP_EOL;
