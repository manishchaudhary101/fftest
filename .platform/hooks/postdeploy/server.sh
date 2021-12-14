#!/bin/bash

chown -R ec2-user /var/app/current

curl -sS https://getcomposer.org/installer | sudo php

mv composer.phar /usr/local/bin/composer

cd /var/app/current/

chmod -R 0777 storage

composer update

composer require inspector-apm/inspector-laravel