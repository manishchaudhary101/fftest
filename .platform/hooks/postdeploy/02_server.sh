#!/bin/bash

chown -R ec2-user /var/app/current

sudo curl -sS https://getcomposer.org/installer | sudo php

sudo mv composer.phar /usr/local/bin/composer

cd /var/app/current/

composer require inspector-apm/inspector-laravel

composer update

