#!/bin/bash
ls
cd var/
ls
cd www/
ls
chown -R ec2-user /var/www/html

sudo curl -sS https://getcomposer.org/installer | sudo php

sudo mv composer.phar /usr/local/bin/composer

cd /var/app/html/

chmod 777 storage/

composer update

composer require inspector-apm/inspector-laravel



