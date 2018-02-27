<?php

declare(strict_types=1);

namespace unreal4u\MQTT\Protocol\Connect;

use Psr\Log\LoggerInterface;
use unreal4u\Dummy\Logger;
use unreal4u\MQTT\Application\Message;
use unreal4u\MQTT\DataTypes\ProtocolVersion;
use unreal4u\MQTT\DataTypes\QoSLevel;

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
    public $host = '';

    /**
     * The port we must connect to
     * @var int
     */
    public $port = 1883;

    /**
     * Unique (per broker) client Id. Can be empty if $cleanSession is set to true.
     *
     * SHOULD be within the character set "0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ"
     *
     * @var string
     */
    private $clientId = '';

    /**
     * The keep alive is a time interval in seconds (defaults to 60), the clients commits to by sending regular PING
     * Request messages to the broker.
     *
     * The broker response with PING Response and this mechanism will allow both sides to determine if the other one is
     * still alive and reachable.
     *
     * @var int
     */
    public $keepAlivePeriod = 60;

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
     * The will message printed out by the server in case of a sudden unexpected disconnect
     * @var string
     */
    private $willMessage = '';

    /**
     * If the client disconnects unexpectedly, set the will message in this will topic
     * @var string
     */
    private $willTopic = '';

    /**
     * QoS Level of the will
     * @var QoSLevel
     */
    private $willQoS;

    /**
     * Whether the will message should be retained by the server
     * @var bool
     */
    private $willRetain = false;

    /**
     * @var ProtocolVersion
     */
    private $protocolVersion;

    /**
     * The 10th byte will contain a series of flags
     *
     * The order of these flags are:
     *
     *   7-6-5-4-3-2-1-0
     * b'0-0-0-0-0-0-0-0'
     *
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
     * @param string $clientId Will default to a clientId set by the broker
     * @param string $host Will default to localhost
     * @param LoggerInterface $logger
     */
    public function __construct(string $clientId = '', string $host = 'localhost', LoggerInterface $logger = null)
    {
        if ($logger === null) {
            $logger = new Logger();
        }
        // Insert name of class within the logger
        $this->logger = $logger->withName(str_replace('unreal4u\\MQTT\\', '', \get_class($this)));

        // Once we have a logger, set the clientId
        $this->setClientId($clientId);
        $this->setProtocolVersion(new ProtocolVersion(self::DEFAULT_PROTOCOL_VERSION));

        $this->host = $host;
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
     * @param string $clientId
     * @return Parameters
     */
    public function setClientId(string $clientId = ''): self
    {
        if ($clientId !== '') {
            $this->clientId = $clientId;
            $clientIdSize = \strlen($this->clientId);
            $utf8ClientIdSize = \mb_strlen($this->clientId);

            if ($clientIdSize !== $utf8ClientIdSize) {
                $this->logger->warning('The broker MAY reject the connection because of invalid characters');
            }

            if ($utf8ClientIdSize > 23) {
                $this->logger->warning('The broker MAY reject the connection because the ClientId is too long');
            }
        } else {
            /*
             * If you ever wind up in this situation, search for MQTT-3.1.3-7 on the following document for more
             * information: http://docs.oasis-open.org/mqtt/mqtt/v3.1.1/os/mqtt-v3.1.1-os.html#_Toc398718067
             */
            $this->logger->warning('ClientId size is 0 bytes. This has several implications, check comments', [
                'file' => __FILE__,
                'line' => __LINE__,
            ]);
            $this->cleanSession = true;
        }

        return $this;
    }

    public function getClientId(): string
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
        return 'tcp://' . $this->host . ':' . $this->port;
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
    public function setKeepAlivePeriod(int $keepAlivePeriod): Parameters
    {
        if ($keepAlivePeriod > 65535 || $keepAlivePeriod < 0) {
            $this->logger->error('Keep alive period must be between 0 and 65535');
            throw new \InvalidArgumentException('Keep alive period must be between 0 and 65535');
        }

        $this->keepAlivePeriod = $keepAlivePeriod;
        return $this;
    }

    /**
     * Sets the 7th bit of the connect flag
     *
     * @see http://docs.oasis-open.org/mqtt/mqtt/v3.1.1/os/mqtt-v3.1.1-os.html#_Toc385349230
     * @param string $username
     * @return Parameters
     */
    public function setUsername(string $username): Parameters
    {
        $this->bitFlag &= ~128;
        if ($username !== '') {
            $this->logger->debug('Username set, setting username flag');
            $this->bitFlag |= 128;
        }
        $this->username = $username;
        return $this;
    }

    /**
     * Sets the 6th bit of the connect flag
     *
     * @see http://docs.oasis-open.org/mqtt/mqtt/v3.1.1/os/mqtt-v3.1.1-os.html#_Toc385349230
     * @param string $password
     * @return Parameters
     */
    public function setPassword(string $password): Parameters
    {
        $this->bitFlag &= ~64;
        if ($password !== '') {
            $this->logger->debug('Password set, setting password flag');
            $this->bitFlag |= 64;
        }
        $this->password = $password;
        return $this;
    }

    /**
     * Is a private method, so can be trusted with just a string name instead of a Topic object
     *
     * @param string $willTopic
     * @return Parameters
     */
    private function setWillTopic(string $willTopic): Parameters
    {
        $this->willTopic = $willTopic;
        $this->logger->debug('Setting will topic');
        return $this;
    }

    /**
     * @param string $willMessage
     * @return Parameters
     */
    private function setWillMessage(string $willMessage): Parameters
    {
        $this->willMessage = $willMessage;
        $this->logger->debug('Setting will message');
        return $this;
    }

    /**
     * Sets the 5th bit of the connect flag
     *
     * @see http://docs.oasis-open.org/mqtt/mqtt/v3.1.1/os/mqtt-v3.1.1-os.html#_Toc385349230
     * @param bool $willRetain
     * @return Parameters
     */
    private function setWillRetain(bool $willRetain): Parameters
    {
        $this->bitFlag &= ~32;
        if ($willRetain === true) {
            $this->logger->debug('Setting will retain flag');
            $this->bitFlag |= 32;
        }
        $this->willRetain = $willRetain;
        return $this;
    }

    /**
     * Determines and sets the 3rd and 4th bits of the connect flag
     *
     * @see http://docs.oasis-open.org/mqtt/mqtt/v3.1.1/os/mqtt-v3.1.1-os.html#_Toc385349230
     * @param QoSLevel $QoSLevel
     * @return Parameters
     */
    private function setWillQoS(QoSLevel $QoSLevel): Parameters
    {
        // Reset first the will QoS bits and proceed to set them
        $this->bitFlag &= ~8; // Third bit: 8
        $this->bitFlag &= ~16; // Fourth bit: 16

        $this->willQoS = $QoSLevel;
        switch ($this->willQoS->getQoSLevel()) {
            case 0:
                // Do nothing as the relevant bits will already have been reset
                break;
            case 1:
                $this->logger->debug('Setting will QoS level 1 flag');
                $this->bitFlag |= 8;
                break;
            case 2:
                $this->logger->debug('Setting will QoS level 2 flag');
                $this->bitFlag |= 16;
                break;
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
    public function setWill(Message $message): Parameters
    {
        // Proceed only if we have a valid message
        if ($message->validateMessage()) {
            $this->bitFlag &= ~4;
            if ($message->getTopicName() !== '') {
                $this->logger->debug('Setting will flag');
                $this->bitFlag |= 4;
            }

            $this
                ->setWillMessage($message->getPayload())
                ->setWillRetain($message->isRetained())
                ->setWillTopic($message->getTopicName())
                ->setWillQoS(new QoSLevel($message->getQoSLevel()));
        }

        return $this;
    }

    /**
     * Sets the 1st bit of the connect flags
     *
     * @param bool $cleanSession
     * @return Parameters
     */
    public function setCleanSession(bool $cleanSession): Parameters
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
        return $this->willTopic;
    }

    /**
     * @return string
     */
    public function getWillMessage(): string
    {
        return $this->willMessage;
    }

    /**
     * @return bool
     */
    public function getWillRetain(): bool
    {
        return $this->willRetain;
    }
}
