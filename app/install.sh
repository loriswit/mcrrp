#!/usr/bin/env bash

set -e
cd "$(dirname "$0")"

composer install
vendor/bin/phinx init

echo

read -p "Enter database name: " name
read -p "Enter database username: " user
read -s -p "Enter database password: " pass

echo

sed -i "s/development_db/$name/g" phinx.yml
sed -i "s/root/$user/g" phinx.yml
sed -i "s/''/$pass/g" phinx.yml

vendor/bin/phinx migrate
