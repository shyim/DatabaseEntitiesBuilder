#!/usr/bin/env bash

for filename in ./dumps/*.sql; do
    mysql -h${1} -u${2} -p${3} ${4} < ${filename}
done