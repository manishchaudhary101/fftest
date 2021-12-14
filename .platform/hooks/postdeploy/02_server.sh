#!/bin/bash

chown -R ec2-user /var/app/current

curl -sS https://getcomposer.org/installer | sudo php

mv composer.phar /usr/local/bin/composer

cd /var/app/current/

composer require inspector-apm/inspector-laravel

composer update

