#!/bin/bash

chown -R ec2-user /var/app/staging

sudo curl -sS https://getcomposer.org/installer | sudo php

sudo mv composer.phar /usr/local/bin/composer

cd /var/app/staging/

chmod 777 storage/

composer update

composer require inspector-apm/inspector-laravel



