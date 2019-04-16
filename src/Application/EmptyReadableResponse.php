<?php

declare(strict_types=1);

namespace unreal4u\MQTT\Application;

use unreal4u\MQTT\Internals\ClientInterface;
use unreal4u\MQTT\Internals\ProtocolBase;
use unreal4u\MQTT\Internals\ReadableContent;
use unreal4u\MQTT\Internals\ReadableContentInterface;

/**
 * This is an example of a payload class that performs some processing on the data
 *
 * This particular case will prepend a datetime to the message itself. It will json_encode() it into the payload and
 * when retrieving it will set the public property $originalPublishDateTime.
 */
final class EmptyReadableResponse extends ProtocolBase implements ReadableContentInterface
{
    const CONTROL_PACKET_VALUE = 0;

    use ReadableContent;

    /**
     * @inheritdoc
     */
    public function getOriginControlPacket(): int
    {
        return 0;
    }

    public function fillObject(string $rawMQTTHeaders, ClientInterface $client): ReadableContentInterface
    {
        return $this;
    }
}
