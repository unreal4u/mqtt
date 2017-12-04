#!/bin/bash

vagrant ssh -- -t 'cd /vagrant; php vendor/bin/phpcs src/ --standard=psr2'
