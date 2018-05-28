# unreal4u/MQTT

Complete PHP7+ MQTT client with full support for 
[the MQTT version 3.1.1 protocol](http://docs.oasis-open.org/mqtt/mqtt/v3.1.1/mqtt-v3.1.1.html). This package is an
entire rewrite of [McFizh/libMQTT](https://github.com/McFizh/libMQTT).

## This project in badges
[![Latest Stable Version](https://poser.pugx.org/unreal4u/mqtt/v/stable)](https://packagist.org/packages/unreal4u/mqtt)
[![Total Downloads](https://poser.pugx.org/unreal4u/mqtt/downloads)](https://packagist.org/packages/unreal4u/mqtt)
[![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/unreal4u/mqtt/badges/quality-score.png?b=master)](https://scrutinizer-ci.com/g/unreal4u/mqtt/?branch=master)
[![Code Coverage](https://scrutinizer-ci.com/g/unreal4u/mqtt/badges/coverage.png?b=master)](https://scrutinizer-ci.com/g/unreal4u/mqtt/?branch=master)
[![Build Status](https://travis-ci.org/unreal4u/mqtt.svg)](https://travis-ci.org/unreal4u/mqtt)
[![License](https://poser.pugx.org/unreal4u/mqtt/license)](https://packagist.org/packages/unreal4u/mqtt)

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

# References
**[mqtt-v3.1.1-plus-errata01]**

MQTT Version 3.1.1 Plus Errata 01. Edited by Andrew Banks and Rahul Gupta. 10 December 2015. OASIS Standard Incorporating Approved Errata 01. 
http://docs.oasis-open.org/mqtt/mqtt/v3.1.1/errata01/os/mqtt-v3.1.1-errata01-os-complete.html. Latest
version: http://docs.oasis-open.org/mqtt/mqtt/v3.1.1/mqtt-v3.1.1.html.

**Original library that served as inspiration for this one**
[McFizh/libMQTT](https://github.com/McFizh/libMQTT)
