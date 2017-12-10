#!/bin/bash

mkdir docs/
vagrant ssh -- -t 'cd /vagrant; php vendor/bin/phpdoc -d src/ -t docs/'
