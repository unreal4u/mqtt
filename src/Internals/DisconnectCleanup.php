<?php

declare(strict_types=1);

namespace unreal4u\MQTT\Internals;

use unreal4u\MQTT\Client;

/**
 * Performs some cleanup on the socket after disconnecting, this class is NOT a part of the MQTT protocol
 */
final class DisconnectCleanup extends ProtocolBase implements ReadableContentInterface
{
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
     * @inheritdoc
     */
    public function performSpecialActions(Client $client, WritableContentInterface $originalRequest): bool
    {
        $successFullyClosed = stream_socket_shutdown($client->socket, STREAM_SHUT_RDWR);
        $information['successfullyClosed'] = $successFullyClosed;
        $this->logger->info('Sent shutdown signal to socket', $information);
        $client->setConnected(false);
        return false;
    }
}
