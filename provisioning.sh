#!/bin/sh
sudo apt-get install -y python-software-properties
sudo add-apt-repository -y ppa:crate/stable
sudo apt-get update
sudo apt-get install -y crate php5 php5-xdebug
