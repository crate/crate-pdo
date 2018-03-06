#!/bin/sh
export DEBIAN_FRONTEND=noninteractive
apt-get update
apt-get install -y python-software-properties
add-apt-repository -y ppa:crate/stable
add-apt-repository -y ppa:openjdk-r/ppa
add-apt-repository -y ppa:ondrej/php
apt-get update
apt-get install -y crate php7.2-cli php7.2-xml php7.2-mbstring

