#!/usr/bin/env bash

set -e
cd $(dirname "$0")

echo -n "Building plugin... "

cd ../plugin
mvn -q install

mkdir -p ../server/plugins
cp target/mcrrp-1.0-SNAPSHOT.jar ../server/plugins/mcrrp.jar

echo "Done!"
