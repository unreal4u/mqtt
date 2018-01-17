<?php

declare(strict_types=1);

namespace unreal4u\MQTT\Internals;

use unreal4u\MQTT\Application\EmptyReadableResponse;
use unreal4u\MQTT\Exceptions\NonAllowedObject;
use unreal4u\MQTT\Protocol\ConnAck;
use unreal4u\MQTT\Protocol\Connect;
use unreal4u\MQTT\Protocol\Disconnect;
use unreal4u\MQTT\Protocol\PingReq;
use unreal4u\MQTT\Protocol\PingResp;
use unreal4u\MQTT\Protocol\PubAck;
use unreal4u\MQTT\Protocol\Publish;
use unreal4u\MQTT\Protocol\PubRec;
use unreal4u\MQTT\Protocol\SubAck;
use unreal4u\MQTT\Protocol\Subscribe;

/**
 * Is able to load in an incoming event and handle it with properly, providing the ability to actively validate
 */
final class EventManager extends ProtocolBase
{
    /**
     * Current object as an object
     * @var ReadableContentInterface
     */
    private $currentObject;

    /**
     * Current object in string format
     * @var string
     */
    private $currentObjectType = '';

    /**
     * @var ReadableContentInterface[]
     */
    private $objectCandidates = [];

    /**
     * A list of all Readable objects that this class may instantiate at some point
     *
     * @see http://docs.oasis-open.org/mqtt/mqtt/v3.1.1/os/mqtt-v3.1.1-os.html#_Toc385349209
     * @var array
     */
    private static $readableObjects = [
        2 => ConnAck::class,
        3 => Publish::class,
        4 => PubAck::class,
        5 => PubRec::class,
        #6 => PubRel::class, TODO Implement PubRel
        #7 => PubComp::class, TODO Implement PubComp
        9 => SubAck::class,
        #11 => UnsubAck::class, TODO Implement UnsubAck
        13 => PingResp::class,
    ];

    /**
     * Not used in this class but handy to have, will maybe be used in the future?
     *
     * @see http://docs.oasis-open.org/mqtt/mqtt/v3.1.1/os/mqtt-v3.1.1-os.html#_Toc385349209
     * @var array
     */
    private static $writableObjects = [
        1 => Connect::class,
        3 => Publish::class,
        4 => PubAck::class,
        5 => PubRec::class,
        #6 => PubRel::class,
        #7 => PubComp::class,
        8 => Subscribe::class,
        #10 => Unsubscribe::class, TODO Implement Unsubscribe
        12 => PingReq::class,
        14 => Disconnect::class,
    ];

    /**
     * Will check within all the Readable objects whether one of those is the correct packet we are looking for
     *
     * @param string $rawMQTTHeaders Arbitrary size of minimum 1 incoming byte(s)
     * @param ClientInterface $client Used if the object itself needs to process some more stuff
     * @return ReadableContentInterface
     * @throws \DomainException
     */
    public function analyzeHeaders(string $rawMQTTHeaders, ClientInterface $client): ReadableContentInterface
    {
        if ($rawMQTTHeaders === '') {
            $this->logger->debug('Empty headers, returning an empty object');
            return new EmptyReadableResponse($this->logger);
        }
        $controlPacketType = \ord($rawMQTTHeaders[0]) >> 4;

        if (array_key_exists($controlPacketType, self::$readableObjects)) {
            $this->currentObjectType = self::$readableObjects[$controlPacketType];
            $this->logger->info('Found corresponding object, instantiating', ['type' => $this->currentObjectType]);
            $this->currentObject = new $this->currentObjectType($this->logger);
            $this->currentObject->instantiateObject($rawMQTTHeaders, $client);
        } else {
            $this->logger->error('Invalid control packet type found', ['controlPacketType' => $controlPacketType]);
            throw new \DomainException(sprintf('Invalid control packet found (%d)', $controlPacketType));
        }

        return $this->currentObject;
    }

    public function addCandidate(ReadableContentInterface ...$restrictionObject): EventManager
    {
        $this->objectCandidates = $restrictionObject;

        return $this;
    }

    public function getObject(): ReadableContentInterface
    {
        foreach ($this->objectCandidates as $restrictionObject) {
            $this->logger->debug('Checking whether currentObject is the correct instance', [
                'currentObject' => \get_class($this->currentObject),
                'objectCheck' => \get_class($restrictionObject),
            ]);
            if ($this->currentObject instanceof $restrictionObject) {
                return $this->currentObject;
            }
        }

        throw new NonAllowedObject('An non allowed object has been found');
    }
}
