<?php

declare(strict_types = 1);

use Monolog\Handler\StreamHandler;
use Monolog\Logger;
use unreal4u\MQTT\Client;
use unreal4u\MQTT\Protocol\Connect;
use unreal4u\MQTT\Protocol\Connect\Parameters;
use unreal4u\MQTT\Protocol\Subscribe;

include __DIR__ . '/00.basics.php';

$logger = new Logger('main');
$logger->pushHandler(new StreamHandler('php://stdout', Logger::DEBUG));

$connectionParameters = new Parameters('localhost', 'subscribeToSomething');
$connectionParameters->setKeepAlivePeriod(5);
$connect = new Connect($logger);
$connect->setConnectionParameters($connectionParameters);

$client = new Client($logger);
$client->sendData($connect);

if ($client->isConnected() === false) {
    throw new DomainException('We are not connected, can not subscribe');
}

$subscribe = new Subscribe($logger);
$subscribe->topic = 'firstTest';

$client->sendData($subscribe);
$i = 0;
while ($i < 60) {
    echo '.';
    $event = $subscribe->checkForEvent($client);
    if ($event instanceof \unreal4u\MQTT\Protocol\Publish) {
        var_dump($event->getMessage());
    }
    #var_dump($event->);
    sleep(1);
    $i++;
}
