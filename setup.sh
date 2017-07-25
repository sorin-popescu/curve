#!/usr/bin/env bash

composer install --no-suggest
sed -i -e s/###//g .gitattributes
rm -f setup.sh
