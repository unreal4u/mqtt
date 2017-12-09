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
     * @inheritdoc
     */
    public function performSpecialActions(Client $client, WritableContentInterface $originalRequest): bool
    {
        $successFullyClosed = stream_socket_shutdown($client->socket, STREAM_SHUT_RDWR);
        $this->logger->info('Sent shutdown signal to socket', ['successFullyClosed' => $successFullyClosed]);
        $client->setConnected(false);
        return true;
    }
}
