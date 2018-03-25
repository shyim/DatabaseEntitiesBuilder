#!/usr/bin/env bash

for filename in ./dumps/*.sql; do
    if [ -n "${DB_PASSWORD}" ]; then
        mysql -h${DB_HOST} -u${DB_USER} -p${DB_PASSWORD} ${DB_NAME} < ${filename}
    else
        mysql -h${DB_HOST} -u${DB_USER} ${DB_NAME} < ${filename}
    fi
done