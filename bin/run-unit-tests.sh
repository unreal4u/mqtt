#!/bin/bash

vagrant ssh -- -t 'cd /vagrant; php vendor/bin/phpunit'
