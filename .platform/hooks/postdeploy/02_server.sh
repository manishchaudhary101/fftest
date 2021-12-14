#!/bin/bash

chown -R ec2-user /var/app/current

cd /var/app/current/

composer require inspector-apm/inspector-laravel

composer update

