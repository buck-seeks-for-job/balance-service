#!/usr/bin/env bash

DIR=$( cd "$( dirname "${BASH_SOURCE[0]}" )" && pwd )

if ! docker ps -q &> /dev/null
then
    echo "You must be in docker group or root"
    exit 1
fi

cd $DIR

docker-compose --project-name balance-service build && \
docker-compose --project-name balance-service up --scale balance-service-php-cli=3

