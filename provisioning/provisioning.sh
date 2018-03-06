#!/bin/sh
export DEBIAN_FRONTEND=noninteractive
apt-get update
apt-get install -y python-software-properties
add-apt-repository -y ppa:ondrej/php
apt-get update
bash -c "$(curl -L install.crate.io)"
apt-get install -y php7.2-cli php7.2-xml php7.2-curl
service crate stop
rm /etc/crate/crate.yml
ln -s /vagrant/provisioning/crate.yml /etc/crate/crate.yml
echo "127.0.0.1 ssl.crate.io" >> /etc/hosts
mkdir /data
chown crate:crate /data
service crate start
