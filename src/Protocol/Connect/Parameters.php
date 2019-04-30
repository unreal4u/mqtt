<?php

declare(strict_types=1);

namespace unreal4u\MQTT\Protocol\Connect;

use Psr\Log\LoggerInterface;
use unreal4u\Dummy\Logger;
use unreal4u\MQTT\DataTypes\BrokerPort;
use unreal4u\MQTT\DataTypes\ClientId;
use unreal4u\MQTT\DataTypes\Message;
use unreal4u\MQTT\DataTypes\ProtocolVersion;

/**
 * Special connection parameters will be defined in this class
 */
final class Parameters
{
    /**
     * The default protocol version this library will be talking with
     */
    const DEFAULT_PROTOCOL_VERSION = '3.1.1';

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * The host we'll be connecting to
     *
     * @var string
     */
    private $host;

    /**
     * The port we will connect to
     * @var BrokerPort
     */
    private $brokerPort;

    /**
     * Unique (per broker) client Id. Can be empty if $cleanSession is set to true.
     *
     * @var ClientId
     */
    private $clientId;

    /**
     * The keep alive is a time interval in seconds (defaults to 60), the clients commits to by sending regular PING
     * Request messages to the broker.
     *
     * The broker response with PING Response and this mechanism will allow both sides to determine if the other one is
     * still alive and reachable.
     *
     * @var int
     */
    private $keepAlivePeriod = 60;

    /**
     * Whether to create a persistent session (default = false).
     *
     * It means that the broker will store all subscriptions for the client and also all missed messages, when
     * subscribing with Quality of Service (QoS) 1 or 2
     * @var bool
     */
    private $cleanSession = false;

    /**
     * The corresponding field for the username flag
     * @var string
     */
    private $username = '';

    /**
     * The corresponding field for the password flag
     * @var string
     */
    private $password = '';

    /**
     * @var Message
     */
    private $will;

    /**
     * @var ProtocolVersion
     */
    private $protocolVersion;

    /**
     * The 10th byte of the Connect call will contain a series of flags
     *
     * The order of these flags are:
     *
     *   7-6-5-4-3-2-1-0
     * b'0-0-0-0-0-0-0-0'
     *
     * Where
     * Bit 7: if username is set, this bit is true
     * Bit 6: if password is set, this bit is true
     * Bit 5: This bit specifies if the Will Message is to be Retained when it is published
     * Bits 4 & 3: These two bits specify the QoS level to be used when publishing the Will Message
     * Bit 2: If the Will Flag is set to 1 this indicates that, if the Connect request is accepted, a Will Message MUST
     *        be stored on the Server and associated with the Network Connection
     * Bit 1: This bit specifies the handling of the Session state
     * Bit 0: Reserved
     *
     * @see http://docs.oasis-open.org/mqtt/mqtt/v3.1.1/errata01/os/mqtt-v3.1.1-errata01-os-complete.html#_Toc442180843
     * @var string
     */
    private $bitFlag = b'00000000';

    /**
     * Builds up the connection parameters
     *
     * @param ClientId $clientId Will default to a clientId set by the broker
     * @param string $host Will default to localhost
     * @param LoggerInterface $logger
     * @throws \unreal4u\MQTT\Exceptions\InvalidBrokerProtocol
     * @throws \unreal4u\MQTT\Exceptions\InvalidBrokerPort
     * @throws \unreal4u\MQTT\Exceptions\Connect\UnacceptableProtocolVersion
     */
    public function __construct(ClientId $clientId = null, string $host = 'localhost', LoggerInterface $logger = null)
    {
        if ($logger === null) {
            $logger = new Logger();
        }
        // Insert name of class within the logger
        $this->logger = $logger->withName(str_replace('unreal4u\\MQTT\\', '', \get_class($this)));

        // Once we have a logger, set the clientId
        if ($clientId === null) {
            $clientId = new ClientId('');
        }
        $this->setClientId($clientId);
        $this->setProtocolVersion(new ProtocolVersion(self::DEFAULT_PROTOCOL_VERSION));
        // Set 1883 as the default port on a non-secured channel
        $this->setBrokerPort(new BrokerPort(1883, 'tcp'));

        $this->host = $host;
    }

    /**
     * Use this function to change the default broker port
     *
     * @param BrokerPort $brokerPort
     * @return Parameters
     */
    public function setBrokerPort(BrokerPort $brokerPort): self
    {
        $this->brokerPort = $brokerPort;
        return $this;
    }

    public function setProtocolVersion(ProtocolVersion $protocolVersion): self
    {
        $this->protocolVersion = $protocolVersion;
        return $this;
    }

    public function getProtocolVersionBinaryRepresentation(): string
    {
        return $this->protocolVersion->getProtocolVersionBinaryRepresentation();
    }

    /**
     * Handles everything related to setting the ClientId
     *
     * @param ClientId $clientId
     * @return Parameters
     */
    public function setClientId(ClientId $clientId): self
    {
        $this->clientId = $clientId;
        $this->logger->debug('Set clientId', ['actualClientString' => (string)$clientId]);
        if ($this->clientId->isEmptyClientId()) {
            $this->logger->debug('Empty clientId detected, forcing clean session bit to true');
            $this->setCleanSession(true);
        }

        return $this;
    }

    public function getClientId(): ClientId
    {
        return $this->clientId;
    }

    /**
     * Returns the connection string
     *
     * @TODO Currently only TCP connections supported, SSL will come
     *
     * @return string
     */
    public function getConnectionUrl(): string
    {
        return sprintf(
            '%s://%s:%d',
            $this->brokerPort->getTransmissionProtocol(),
            $this->host,
            $this->brokerPort->getBrokerPort()
        );
    }

    /**
     * Returns the set of flags we are making the connection with
     *
     * @return int
     */
    public function getFlags(): int
    {
        return (int)$this->bitFlag;
    }

    /**
     * Keep alive period is measured in positive seconds. The maximum is 18h, 12m and 15s, equivalent to 65535 seconds
     *
     * @param int $keepAlivePeriod
     * @return Parameters
     * @throws \InvalidArgumentException
     */
    public function setKeepAlivePeriod(int $keepAlivePeriod): self
    {
        if ($keepAlivePeriod > 65535 || $keepAlivePeriod < 0) {
            $this->logger->error('Keep alive period must be between 0 and 65535');
            throw new \InvalidArgumentException('Keep alive period must be between 0 and 65535');
        }

        $this->keepAlivePeriod = $keepAlivePeriod;
        return $this;
    }

    /**
     * Sets the 6th and 7th bit of the connect flag
     *
     * @param string $username
     * @param string $password
     * @return Parameters
     */
    public function setCredentials(string $username, string $password): self
    {
        $this->bitFlag &= ~64;
        $this->bitFlag &= ~128;

        if ($username !== '') {
            $this->logger->debug('Username set, setting username flag');
            $this->bitFlag |= 128;
            $this->username = $username;
        }

        if ($password !== '') {
            $this->logger->debug('Password set, setting password flag');
            $this->bitFlag |= 64;
            $this->password = $password;
        }

        return $this;
    }

    /**
     * Sets the 5th bit of the connect flag
     *
     * @see http://docs.oasis-open.org/mqtt/mqtt/v3.1.1/os/mqtt-v3.1.1-os.html#_Toc385349230
     * @param bool $willRetain
     * @return Parameters
     */
    private function setWillRetainBit(bool $willRetain): self
    {
        $this->bitFlag &= ~32;
        if ($willRetain === true) {
            $this->logger->debug('Setting will retain flag');
            $this->bitFlag |= 32;
        }
        return $this;
    }

    /**
     * Determines and sets the 3rd and 4th bits of the connect flag
     *
     * @see http://docs.oasis-open.org/mqtt/mqtt/v3.1.1/os/mqtt-v3.1.1-os.html#_Toc385349230
     * @param int $QoSLevel
     * @return Parameters
     */
    private function setWillQoSLevelBit(int $QoSLevel): self
    {
        // Reset first the will QoS bits and proceed to set them
        $this->bitFlag &= ~8; // Third bit: 8
        $this->bitFlag &= ~16; // Fourth bit: 16

        if ($QoSLevel !== 0) {
            $this->logger->debug(sprintf(
                'Setting will QoS level %d flag (bit %d)',
                $QoSLevel,
                $QoSLevel * 8
            ));

            $this->bitFlag |= ($QoSLevel * 8);
        }

        return $this;
    }

    /**
     * Sets the given will. Will also set the 2nd bit of the connect flags if a message is provided
     *
     * @see http://docs.oasis-open.org/mqtt/mqtt/v3.1.1/os/mqtt-v3.1.1-os.html#_Toc385349230
     * @param Message $message
     * @return Parameters
     * @throws \unreal4u\MQTT\Exceptions\InvalidQoSLevel
     * @throws \unreal4u\MQTT\Exceptions\MissingTopicName
     * @throws \unreal4u\MQTT\Exceptions\MessageTooBig
     */
    public function setWill(Message $message): self
    {
        // Proceed only if we have a valid message
        $this->bitFlag &= ~4;
        if ($message->getTopicName() !== '') {
            $this->logger->debug('Setting will flag');
            $this->bitFlag |= 4;
        }

        $this->will = $message;
        $this
            ->setWillRetainBit($message->isRetained())
            ->setWillQoSLevelBit($message->getQoSLevel());

        return $this;
    }

    /**
     * Sets the 1st bit of the connect flags
     *
     * @param bool $cleanSession
     * @return Parameters
     */
    public function setCleanSession(bool $cleanSession): self
    {
        $this->bitFlag &= ~2;
        if ($cleanSession === true) {
            $this->logger->debug('Clean session flag set');
            $this->bitFlag |= 2;
        }
        $this->cleanSession = $cleanSession;
        return $this;
    }

    /**
     * @return int
     */
    public function getKeepAlivePeriod(): int
    {
        return $this->keepAlivePeriod;
    }

    /**
     * @return bool
     */
    public function getCleanSession(): bool
    {
        return $this->cleanSession;
    }

    /**
     * @return string
     */
    public function getUsername(): string
    {
        return $this->username;
    }

    /**
     * @return string
     */
    public function getPassword(): string
    {
        return $this->password;
    }

    /**
     * @return string
     */
    public function getWillTopic(): string
    {
        if ($this->will === null) {
            return '';
        }

        return $this->will->getTopicName();
    }

    /**
     * @return string
     */
    public function getWillMessage(): string
    {
        if ($this->will === null) {
            return '';
        }

        return $this->will->getPayload();
    }

    /**
     * @return bool
     */
    public function getWillRetain(): bool
    {
        return $this->will->isRetained();
    }
}
