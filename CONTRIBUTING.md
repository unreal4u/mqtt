# Want to contribute?

First of all: ALL contributions are welcome! 

That being said, this package works with vagrant, a virtual machine that will setup all the basics for you, so you don't
lose time setting everything up and installing all dependencies by hand.  
Here are some guidelines that may ease up development for you:

* Ensure virtualbox is installed: [https://www.virtualbox.org/wiki/Downloads](https://www.virtualbox.org/wiki/Downloads)
* Ensure vagrant is installed: [https://www.vagrantup.com](https://www.vagrantup.com)
* Ensure plugin vagrant-vbguest is installed: [https://github.com/dotless-de/vagrant-vbguest](https://github.com/dotless-de/vagrant-vbguest)

```bash
vagrant plugin install vagrant-vbguest
```

* After all dependencies are installed, execute the following in project directory:

```bash
vagrant up
vagrant ssh
cd /vagrant/
# Note: minimum supported version is 7.0, so install all dependencies for THAT version
php70 /usr/bin/composer.phar update -o
# Enjoy!
```

# Testing

## Unit tests

To run all unit tests:

```bash
vagrant ssh
cd /vagrant/
php vendor/bin/phpunit
# Enjoy!
# To see code coverage, you can simply initialize a server:
sudo firewall-cmd --add-port=8889/tcp
cd report/
php -S 192.168.33.11:8889
```

Navigate with your browser to http://192.168.33.11:8889 and behold.

## Run code inspector

This project uses phpcs to validate the style guide (PSR-2, will switch to PSR-12 once that becomes an official final
standard).
To run the suite:
```bash
vagrant ssh
cd /vagrant/
php vendor/bin/phpcs src/ --standard=psr2
```

## Creating documentation

Please don't commit the auto-generated documentation. To generate it:

```bash
vagrant ssh
cd /vagrant/
# Execute this in php7.1 because 7.2 still is a bit buggy with the 7.0 installed version
php71 vendor/bin/phpdoc -d src/ -t docs/
# To see the docs, you can simply initialize a server:
sudo firewall-cmd --add-port=8889/tcp
cd docs/
php -S 192.168.33.11:8889
```

Navigate with your browser to http://192.168.33.11:8889 and behold.
