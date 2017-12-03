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

define('MAXIMUM', 2);
if ($client->isConnected()) {
    $payload = new SimplePayload();
    $message = new Message();
    $message->setTopicName('firstTest');
    $message->setQoSLevel(1);
    $publish = new Publish();

    for ($i = 1; $i <= MAXIMUM; $i++) {
        $payload->setPayload(sprintf('Hello world!! (%d / %d)', $i, MAXIMUM));
        $message->setPayload($payload);
        $publish->setMessage($message);
        var_dump($client->sendData($publish));
        echo '.';
    }
}
echo PHP_EOL;
