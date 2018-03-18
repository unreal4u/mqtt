<?php

/**
 * Example file of an unsubscription while subscribing to multiple topics
 */

declare(strict_types=1);

use Monolog\Handler\StreamHandler;
use Monolog\Logger;
use Psr\Log\LoggerInterface;
use unreal4u\MQTT\Client;
use unreal4u\MQTT\DataTypes\ClientId;
use unreal4u\MQTT\DataTypes\Topic;
use unreal4u\MQTT\Protocol\Connect;
use unreal4u\MQTT\Protocol\Connect\Parameters;
use unreal4u\MQTT\Protocol\Subscribe;
use unreal4u\MQTT\Protocol\Unsubscribe;

include __DIR__ . '/00.basics.php';

$logger = new Logger('main');
$logger->pushHandler(new StreamHandler('php://stdout', Logger::DEBUG));

$connectionParameters = new Parameters(new ClientId(basename(__FILE__)));
$connectionParameters->setUsername('testuser');
$connectionParameters->setPassword('userpass');

$connect = new Connect();
$connect->setConnectionParameters($connectionParameters);

try {
    $client = new Client($logger);
    $client->processObject($connect);
} catch (\Exception $e) {
    printf($e->getMessage());
    die();
}
$logger->info('Client connected, continuing...');

if ($client->isConnected() === false) {
    throw new DomainException('We are not connected, can not subscribe');
}

$mainTopic = new Topic(COMMON_TOPICNAME);
/*
 * We are subscribing to all sensors/# topics. The notation used here is the default supported by the MQTT protocol
 *
 * For more information about this, please check:
 * http://docs.oasis-open.org/mqtt/mqtt/v3.1.1/os/mqtt-v3.1.1-os.html#_Toc398718106
 */
$secondaryTopic = new Topic('sensors/#');

$subscribe = new Subscribe($logger);
$subscribe->addTopics($mainTopic, $secondaryTopic);

/*
 * The way this works is by providing a callable as the third argument to the loop function, this will execute any given
 * piece of code right after subscribing, but before beginning with the loop.
 *
 * Any callable can use one optional argument: the Logger. This may or may not change in the future.
 */
/** @var \unreal4u\MQTT\Application\Message $message */
foreach ($subscribe->loop($client, 100000, function (LoggerInterface $logger) use ($client) {
    $unsubscribe = new Unsubscribe($logger);
    // We will unsubscribe specifically from the SECONDARY_TOPICNAME topic (but not the rest)
    $unsubscribe->addTopics(new Topic(SECONDARY_TOPICNAME));
    $client->processObject($unsubscribe);
}) as $message) {
    // Any message here should NOT be within the SECONDARY_TOPICNAME topic
    printf(
        '%s-- Payload detected on topic "%s" (QoS lvl %d): %s + %s%s',
        PHP_EOL,
        $message->getTopicName(),
        $message->getQoSLevel(),
        PHP_EOL,
        $message->getPayload(),
        PHP_EOL
    );
}
