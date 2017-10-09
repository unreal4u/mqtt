<?php

declare(strict_types=1);

namespace unreal4u\MQTT\Internals;

use unreal4u\MQTT\Client;

/**
 * Performs some cleanup on the socket after disconnecting, this class is NOT a part of the MQTT protocol
 */
final class DisconnectCleanup implements ReadableContentInterface
{
    use CommonFunctionality;
    use ReadableContent;

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
        $successFullyClosed = stream_socket_shutdown($client->socket, STREAM_SHUT_RDWR);
        $information['successfullyClosed'] = $successFullyClosed;
        $this->logger->info('Sent shutdown signal to socket', $information);
        $client->setConnected(false);
        return false;
    }
}
