#!/bin/sh
wget -O - http://dl.hhvm.com/conf/hhvm.gpg.key | sudo apt-key add -
echo deb http://dl.hhvm.com/ubuntu trusty main | sudo tee /etc/apt/sources.list.d/hhvm.list
sudo apt-get update
sudo apt-get install -y python-software-properties
sudo add-apt-repository -y ppa:crate/stable
sudo apt-get update
sudo apt-get install -y crate hhvm php5-cli php5-xdebug
