# unreal4u/MQTT

Simple MQTT library for PHP 7, with (for now) partial support for 
[MQTT version 3.1.1](http://docs.oasis-open.org/mqtt/mqtt/v3.1.1/mqtt-v3.1.1.html), it is a rewrite of 
[McFizh/libMQTT](https://github.com/McFizh/libMQTT). 

## Important Badges
[![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/unreal4u/mqtt/badges/quality-score.png?b=master)](https://scrutinizer-ci.com/g/unreal4u/mqtt/?branch=master)
[![Code Coverage](https://scrutinizer-ci.com/g/unreal4u/mqtt/badges/coverage.png?b=master)](https://scrutinizer-ci.com/g/unreal4u/mqtt/?branch=master)
[![Build Status](https://travis-ci.org/unreal4u/mqtt.svg)](https://travis-ci.org/unreal4u/mqtt)

## Stability notes

Please note that for the time being, this is still work in progress! A version will be launched when I believe it to be
ready for production environments.

# What is MQTT?

## Definitions

MQTT is a Client Server publish/subscribe messaging transport protocol. It is light weight, open, simple, and designed so as to be easy to implement. These
characteristics make it ideal for use in many situations, including constrained environments such as for communication in Machine to Machine (M2M) and
Internet of Things (IoT) contexts where a small code footprint is required and/or network bandwidth is at a premium.

The protocol runs over TCP/IP, or over other network protocols that provide ordered, lossless, bi-directional connections. Its features include:

- Use of the publish/subscribe message pattern which provides one-to-many message distribution and decoupling of applications.
- A messaging transport that is agnostic to the content of the payload.
- Three qualities of service for message delivery:
  - "At most once", where messages are delivered according to the best efforts of the operating environment. Message loss can occur. This level could be
  used, for example, with ambient sensor data where it does not matter if an individual reading is lost as the next one will be published soon after.
  - "At least once", where messages are assured to arrive but duplicates can occur.
  - "Exactly once", where message are assured to arrive exactly once. This level could be used, for example, with billing systems where duplicate or lost
  messages could lead to incorrect charges being applied.
- A small transport overhead and protocol exchanges minimized to reduce network traffic.
- A mechanism to notify interested parties when an abnormal disconnection occurs.

## Clarifications

MQTT works with a Client and Server model, this means that:
- A Client will connect to a Server (Also known as a broker).
- This API implements the Client-side and not the server side.
- A client can perform different operations, such as subscribing and publishing.
- A publish action from the client is simply putting a message on the server.
- A subscription can be seen as a publish done by the server to the client.

With that knowledge, QoS works in the following way:
- QoS level 0 (default): A publish message may never arrive at the server.
- QoS level 1: A publish message must arrive at the server, however, this may occur several times, for example, during an
intermittent connection to the server.
- QoS level 2: A publish message must arrive at the server, but exactly one time. The way this is dealt with is to effectively
start a conversation with the broker in order to assure messages arrive only once.

Little note on retainability: it is possible to indicate to the broker that a message must be retained. This is done by the
retain bit and has nothing to do with the QoS of the message.  
A retained message will be shown to every subscribed client + all the new ones that might subscibe to that same topic.

# Development environment

* Ensure virtualbox is installed: [https://www.virtualbox.org/wiki/Downloads](https://www.virtualbox.org/wiki/Downloads)
* Ensure vagrant is installed: [https://www.vagrantup.com](https://www.vagrantup.com)
* Ensure plugin vagrant-vbguest is installed: [https://github.com/dotless-de/vagrant-vbguest](https://github.com/dotless-de/vagrant-vbguest)

```bash
vagrant plugin install vagrant-vbguest
```

After all dependencies are installed, execute the following in project directory:

```bash
vagrant up
vagrant ssh
cd /vagrant/
composer.phar update -o
# Enjoy!
```

To run all unit tests:

```bash
vagrant ssh
cd /vagrant/
php71 vendor/bin/phpunit
# Enjoy!
```

# References
**[mqtt-v3.1.1-plus-errata01]**

MQTT Version 3.1.1 Plus Errata 01. Edited by Andrew Banks and Rahul Gupta. 10 December 2015. OASIS Standard Incorporating Approved Errata 01. 
http://docs.oasis-open.org/mqtt/mqtt/v3.1.1/errata01/os/mqtt-v3.1.1-errata01-os-complete.html. Latest
version: http://docs.oasis-open.org/mqtt/mqtt/v3.1.1/mqtt-v3.1.1.html.

**Original library that served as inspiration for this one**
[McFizh/libMQTT](https://github.com/McFizh/libMQTT)
