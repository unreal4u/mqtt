# unreal4u/MQTT

Complete PHP7+ MQTT client with full support for 
[the MQTT version 3.1.1 protocol](http://docs.oasis-open.org/mqtt/mqtt/v3.1.1/mqtt-v3.1.1.html). This package is an
entire rewrite of [McFizh/libMQTT](https://github.com/McFizh/libMQTT).

## This project in badges
[![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/unreal4u/mqtt/badges/quality-score.png?b=master)](https://scrutinizer-ci.com/g/unreal4u/mqtt/?branch=master)
[![Code Coverage](https://scrutinizer-ci.com/g/unreal4u/mqtt/badges/coverage.png?b=master)](https://scrutinizer-ci.com/g/unreal4u/mqtt/?branch=master)
[![Build Status](https://travis-ci.org/unreal4u/mqtt.svg)](https://travis-ci.org/unreal4u/mqtt)

## Stability notes

Please note that for the time being, this is still work in progress! A version will be launched when I believe it to be
ready for production environments.

## What is MQTT?

Please read the [following wiki page](https://github.com/unreal4u/mqtt/wiki/What-is-MQTT) for that :)

## Capabilities of this package: 

This package is able to:
- Connect to the broker (SSL not tested yet). You can connect with virtually all optional parameters the protocol
supports, including Will Message. The only exception to the rule is the clean session flag. This is not tested and may
or may not work as intended.
- Publish QoS level 0, 1 and 2 messages. All protocol supported parameters are also supported, such as retained messages
and other options.
- Subscribe on QoS level 0, 1 and 2 topics. Connection handling will be done automatically, no need to fiddle with
PingRequests and alike.
- Filters of topics are those used on the protocol itself, which eliminates the likeliness of bugs that may occur from
incorrectly parsing such filters.

This package uses sockets to communicate (a)synchronously with the broker. If you don't want this, you are free to
create your own client, for which you'll just have to implement an interface.

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
