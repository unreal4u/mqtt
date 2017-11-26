<?php

declare(strict_types=1);

namespace unreal4u\MQTT\Protocol;

use unreal4u\MQTT\Client;
use unreal4u\MQTT\Exceptions\Connect\BadUsernameOrPassword;
use unreal4u\MQTT\Exceptions\Connect\GenericError;
use unreal4u\MQTT\Exceptions\Connect\IdentifierRejected;
use unreal4u\MQTT\Exceptions\Connect\NotAuthorized;
use unreal4u\MQTT\Exceptions\Connect\ServerUnavailable;
use unreal4u\MQTT\Exceptions\Connect\UnacceptableProtocolVersion;
use unreal4u\MQTT\Internals\CommonFunctionality;
use unreal4u\MQTT\Internals\ReadableContent;
use unreal4u\MQTT\Internals\ReadableContentInterface;

final class Connack implements ReadableContentInterface
{
    use CommonFunctionality;
    use ReadableContent;

    const CONTROL_PACKET_VALUE = 2;

    /**
     * The connect return code. If a server sends a CONNACK packet containing a non-zero return code it MUST then close
     * the Network Connection
     *
     * @see http://docs.oasis-open.org/mqtt/mqtt/v3.1.1/os/mqtt-v3.1.1-os.html#_Table_3.1_-
     *
     * 0 = Connection accepted
     * 1 = Connection Refused, unacceptable protocol version
     * 2 = Connection Refused, identifier rejected
     * 3 = Connection Refused, Server unavailable
     * 4 = Connection Refused, bad user name or password
     * 5 = Connection Refused, not authorized
     * 6-255 = Reserved for future use
     * @var int
     */
    public $connectReturnCode;

    public function fillObject(): ReadableContentInterface
    {
        $this->connectReturnCode = \ord($this->rawMQTTHeaders[3]);
        switch ($this->connectReturnCode) {
            case 0:
                // Everything correct, do nothing
                break;
            case 1:
                throw new UnacceptableProtocolVersion(
                    'The Server does not support the level of the MQTT protocol requested by the Client'
                );
                break;
            case 2:
                throw new IdentifierRejected('The Client identifier is correct UTF-8 but not allowed by the Server');
                break;
            case 3:
                throw new ServerUnavailable('The Network Connection has been made but the MQTT service is unavailable');
                break;
            case 4:
                throw new BadUsernameOrPassword('The data in the user name or password is malformed');
                break;
            case 5:
                throw new NotAuthorized('The Client is not authorized to connect');
                break;
            default:
                throw new GenericError(sprintf(
                    'Reserved for future use or error not implemented yet (Error code: %d)',
                    $this->connectReturnCode
                ));
                break;
        }

        return $this;
    }

    public function performSpecialActions(Client $client): bool
    {
        $client
            ->setConnected(true)
            ->updateLastCommunication();

        return true;
    }
}
