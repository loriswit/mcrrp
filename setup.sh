#!/usr/bin/env bash

set -e
cd "$(dirname "$0")"

separator=--------------------------------------

echo Welcome to MCRRP setup assistant
echo
echo Step 1. Creating database
echo $separator
echo Please make sure that MariaDB server is running.

read -p "Enter your database username: " user
read -s -p "Enter your database password: " pass
echo
read -p "Enter the new database name to be created: " name

statement="CREATE DATABASE IF NOT EXISTS $name"
if [ -z $pass ]; then
    mysql -u $user -e "$statement"
else
    mysql -u $user -p$pass -e "$statement"
fi

echo
echo Step 2. Installing web app dependencies
echo $separator

cd app
composer install

echo
echo Step 3. Running migrations
echo $separator

if [ -f phinx.yml ]; then
    rm phinx.yml
fi
vendor/bin/phinx init

sed -i "s/development_db/$name/g" phinx.yml
sed -i "s/root/$user/g" phinx.yml
if [ ! -z $pass ]; then
    sed -i "s/''/$pass/g" phinx.yml
fi

vendor/bin/phinx migrate

echo
echo Step 4. Installing Spigot
echo $separator
cd ..
mkdir -p server/plugins
curl -o server/spigot.jar https://cdn.getbukkit.org/spigot/spigot-1.12.jar
echo

echo
echo Step 5. Building MCRRP plugin
echo $separator

cd plugin
mvn install
cp target/mcrrp-1.0-SNAPSHOT.jar ../server/plugins/mcrrp.jar

echo
echo Step 6. Creating server startup script
echo $separator

read -p "Enter maximum size of memory allocation: " memory
cd ..
echo -e "#!/usr/bin/env bash\n\ncd server\njava -Xms512M -Xmx$memory -jar spigot.jar\n" > start.sh
echo -e "created \e[33m./start.sh\e[0m"

echo
echo Step 7. End-user license agreement
echo $separator

echo -e "You need to agree to the EULA in order to run the server: \e[33mhttps://account.mojang.com/documents/minecraft_eula\e[0m"
read -p "Do you agree to the EULA? (y/n) " -n 1
echo

if [[ $REPLY =~ ^[Yy]$ ]]; then
    echo -e "#By changing the setting below to TRUE you are indicating your agreement to our EULA (https://account.mojang.com/documents/minecraft_eula)." > server/eula.txt
    echo -e "#$(date)" >> server/eula.txt
    echo -e "eula=true" >> server/eula.txt
fi

echo
echo Installation finished!
echo -e "You need to set Apache's document root to the \e[33m./app\e[0m directory."
