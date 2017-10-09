<?php

declare(strict_types=1);

namespace unreal4u\MQTT\Protocol\Connect;

final class Parameters
{
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
    public $clientId = '';

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
     * Whether the will message should be retained by the server
     * @var bool
     */
    private $willRetain = false;

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
     * @param string $host
     * @param string $clientId
     * @throws \InvalidArgumentException
     */
    public function __construct(string $host, string $clientId = '')
    {
        if ($host === '') {
            throw new \InvalidArgumentException('Host must be set on construction');
        }

        if ($clientId !== '') {
            $this->clientId = $clientId;
        }

        $this->host = $host;
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
            throw new \InvalidArgumentException('Keep alive period must be between 0 and 65535');
        }

        $this->keepAlivePeriod = $keepAlivePeriod;
        return $this;
    }

    /**
     * @param string $username
     * @return Parameters
     */
    public function setUsername(string $username): Parameters
    {
        $this->bitFlag &= ~128;
        if ($username !== '') {
            $this->bitFlag |= 128;
        }
        $this->username = $username;
        return $this;
    }

    /**
     * @param string $password
     * @return Parameters
     */
    public function setPassword(string $password): Parameters
    {
        $this->bitFlag &= ~64;
        if ($password !== '') {
            $this->bitFlag |= 64;
        }
        $this->password = $password;
        return $this;
    }

    /**
     * @param bool $willRetain
     * @return Parameters
     */
    public function setWillRetain(bool $willRetain): Parameters
    {
        $this->bitFlag &= ~32;
        if ($willRetain === true) {
            $this->bitFlag |= 32;
        }
        $this->willRetain = $willRetain;
        return $this;
    }

    /**
     * @param string $willTopic
     * @return Parameters
     */
    public function setWillTopic(string $willTopic): Parameters
    {
        $this->bitFlag &= ~4;
        if ($willTopic !== '') {
            $this->bitFlag |= 4;
        }
        $this->willTopic = $willTopic;
        return $this;
    }

    /**
     * @param string $willMessage
     * @return Parameters
     */
    public function setWillMessage(string $willMessage): Parameters
    {
        $this->bitFlag &= ~4;
        if ($willMessage !== '') {
            $this->bitFlag |= 4;
        }
        $this->willMessage = $willMessage;
        return $this;
    }

    /**
     * @param bool $cleanSession
     * @return Parameters
     */
    public function setCleanSession(bool $cleanSession): Parameters
    {
        $this->bitFlag &= ~2;
        if ($cleanSession === true) {
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
