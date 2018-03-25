#!/usr/bin/env bash

if [ -z "$DB_HOST" ]; then
    read -p "DB Host: " DB_HOST
    read -p "DB User: " DB_USER
    read -p "DB Password: " DB_PASSWORD
    read -p "DB Name: " DB_NAME

    export DB_HOST=${DB_HOST}
    export DB_USER=${DB_USER}
    export DB_PASSWORD=${DB_PASSWORD}
    export DB_NAME=${DB_NAME}
fi

./prepareDatabase.sh $DB_HOST $DB_USER $DB_PASSWORD $DB_NAME

php ../vendor/bin/phpunit -c .