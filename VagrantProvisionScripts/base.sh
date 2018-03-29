#!/usr/bin/env bash

# Define latest PHP version as the current one
MAIN_PHP_VERSION="72"

# ++++++++++ Disclaimer: DON'T disable SELinux on production machines!!!!11!!1! ++++++++++++
# That being said, this is a development machine which doesn't require the extra protection SELinux provides. As I am
# too lazy to configure stuff properly on environments that won't last long, I just went for the easy way. Don't be like
# me and enable wherever possible.

# disable selinux for current boot
setenforce 0
# disable selinux permanently
sed -i 's/SELINUX=enforcing/SELINUX=disabled/' /etc/sysconfig/selinux
sed -i 's/SELINUX=enforcing/SELINUX=disabled/' /etc/selinux/config

yum install -q -y epel-release
# Enable installation after epel is installed
yum -q -y install lynx ntp vim-enhanced wget unzip git nginx mosquitto

# Enable services
systemctl enable ntpd
systemctl start ntpd
systemctl enable firewalld
systemctl start firewalld

# Set the correct time
ntpdate -u pool.ntp.org

yum install -q -y http://rpms.remirepo.net/enterprise/remi-release-7.rpm

# Install PHP in all the supported version this library... supports
declare -a PHP_VERSIONS=("70" "71" "72")
for php_version in "${PHP_VERSIONS[@]}"
do
    :
    yum install -q -y \
  php${php_version}-php \
  php${php_version}-php-opcache \
  php${php_version}-php-mbstring \
  php${php_version}-php-xml \
  php${php_version}-php-pecl-xdebug \
  php${php_version}-php-dbg
done

# Enable direct php and phpdbg commands to the latest PHP version
ln -s /usr/bin/php${MAIN_PHP_VERSION} /usr/bin/php
ln -s /usr/bin/php${MAIN_PHP_VERSION}-phpdbg /usr/bin/phpdbg

curl -sS https://getcomposer.org/installer | php
mv composer.phar /usr/bin/

firewall-cmd --zone=public --add-service http
firewall-cmd --zone=public --add-service http --permanent

# Open up Mosquitto for network and set a default user/passwd combination
touch /etc/mosquitto/passwd
mosquitto_passwd -b /etc/mosquitto/passwd testuser userpass
# Enable outside communication. Don't do this on production machines without verifying first that authentication works
# properly. Otherwise, these kind of stuff WILL happen: https://thehackernews.com/2017/07/memcached-vulnerabilities.html
firewall-cmd --zone=public --add-port=1883/tcp
firewall-cmd --zone=public --add-port=1883/tcp --permanent
systemctl enable mosquitto
systemctl start mosquitto
