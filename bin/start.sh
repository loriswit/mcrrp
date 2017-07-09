#!/usr/bin/env bash

set -E
cd $(dirname "$0")

trap 'echo -e "Server startup failed. Please run \e[33mbin/setup.sh\e[0m to fix the problem." ; exit' ERR

cd ..
eula=$(bin/config.sh settings eula)
sed -i "s/eula=.*$/eula=$eula/m" server/eula.txt

online=$(bin/config.sh settings online)
sed -i "s/online-mode=.*$/online-mode=$online/m" server/server.properties

init=$(bin/config.sh memory initial)
max=$(bin/config.sh memory max)

cd server
java -Xms$init -Xmx$max -jar spigot.jar

