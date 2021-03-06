#!/usr/bin/env bash

DIR=$( cd "$( dirname "${BASH_SOURCE[0]}" )" && pwd )

cd $DIR

docker-compose --project-name balance-service run balance-service-php-cli php vendor/phpunit/phpunit/phpunit -c tests/phpunit.xml
