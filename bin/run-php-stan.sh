#!/bin/bash

vagrant ssh -- -t 'cd /vagrant; php vendor/bin/phpstan analyze --level 1 -- src/'
