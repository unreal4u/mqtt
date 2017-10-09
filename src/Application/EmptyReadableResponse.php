<?php

declare(strict_types=1);

namespace unreal4u\MQTT\Application;

use unreal4u\MQTT\Client;
use unreal4u\MQTT\Internals\CommonFunctionality;
use unreal4u\MQTT\Internals\ReadableBase;
use unreal4u\MQTT\Internals\ReadableContentInterface;

/**
 * This is an example of a payload class that performs some processing on the data
 *
 * This particular case will prepend a datetime to the message itself. It will json_encode() it into the payload and
 * when retrieving it will set the public property $originalPublishDateTime.
 */
final class EmptyReadableResponse implements ReadableContentInterface
{
    use CommonFunctionality;
    use ReadableBase;

    /**
     * Will perform sanity checks and fill in the Readable object with data
     * @return ReadableContentInterface
     */
    public function fillObject(): ReadableContentInterface
    {
        return $this;
    }

    /**
     * Some operations require setting some things in the client, this hook will do so
     *
     * @param Client $client
     * @return bool
     */
    public function performSpecialActions(Client $client): bool
    {
        return false;
    }
}
