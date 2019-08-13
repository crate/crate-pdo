#!/bin/sh
export DEBIAN_FRONTEND=noninteractive
apt-get update
apt-get upgrade
apt-get install -y python-software-properties
add-apt-repository -y ppa:openjdk-r/ppa
add-apt-repository -y ppa:ondrej/php
wget https://cdn.crate.io/downloads/deb/DEB-GPG-KEY-crate
apt-key add DEB-GPG-KEY-crate
. /etc/os-release
echo "deb https://cdn.crate.io/downloads/deb/stable/ $UBUNTU_CODENAME main" > /etc/apt/sources.list.d/crate-stable-$UBUNTU_CODENAME.list
apt-get update
apt-get install -y openjdk-11-jre-headless crate php7.2-cli php7.2-xml php7.2-mbstring php7.2-xdebug
service crate stop
rm /etc/crate/crate.yml
ln -s /vagrant/test/provisioning/crate.yml /etc/crate/crate.yml
echo "127.0.0.1 ssl.crate.io" >> /etc/hosts
mkdir /data
chown crate:crate /data
service crate start
