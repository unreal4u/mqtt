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
use unreal4u\MQTT\DataTypes\TopicFilter;
use unreal4u\MQTT\Protocol\Connect;
use unreal4u\MQTT\Protocol\Connect\Parameters;
use unreal4u\MQTT\Protocol\Subscribe;
use unreal4u\MQTT\Protocol\Unsubscribe;

include __DIR__ . '/00.basics.php';

$logger = new Logger('main');
$logger->pushHandler(new StreamHandler('php://stdout', Logger::DEBUG));

$connectionParameters = new Parameters(new ClientId(basename(__FILE__)));
$connectionParameters->setCredentials('testuser', 'userpass');

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

$mainTopic = new TopicFilter(COMMON_TOPICNAME);
/*
 * For more information about this, please check:
 * http://docs.oasis-open.org/mqtt/mqtt/v3.1.1/os/mqtt-v3.1.1-os.html#_Toc398718106
 *
 * The main idea is that we could subscribe to sensors/# and ignore messages from sensors/baseroom. HOWEVER, in the
 * official MQTT protocol it states that:
 *
 * | The Topic Filters (whether they contain wildcards or not) supplied in an UNSUBSCRIBE packet MUST be compared
 * | character-by-character with the current set of Topic Filters held by the Server for the Client. If any filter
 * | matches exactly then its owning Subscription is deleted, otherwise no additional processing occurs [MQTT-3.10.4-1].
 *
 * This represents a bit of a problem: it is only really useful for known topics from which we want to Unsubscribe,
 * comparing them character-by-character. Is is thus not possible to subscribe to sensors/# and at the same time, ignore
 * sensors/baseroom.
 *
 * However, the possibility remains, it can be useful for other cool stuff later on.
 */
$secondaryTopic = new TopicFilter(SECONDARY_TOPICNAME);

$subscribe = new Subscribe($logger);
$subscribe->addTopics($mainTopic, $secondaryTopic);

/*
 * The way this works is by providing a callable as the third argument to the loop function, this will execute any given
 * piece of code right after subscribing, but before beginning with the loop.
 *
 * Any callable can use one optional argument: the Logger. This may or may not change in the future.
 */
/** @var \unreal4u\MQTT\DataTypes\Message $message */
foreach ($subscribe->loop($client, 100000, function (LoggerInterface $logger) use ($client) {
    $unsubscribe = new Unsubscribe($logger);
    // We will unsubscribe specifically from the SECONDARY_TOPICNAME topic (but not the rest)
    $unsubscribe->addTopics(new TopicFilter(SECONDARY_TOPICNAME));
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
