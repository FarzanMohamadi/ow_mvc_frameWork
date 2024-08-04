CID=${CI_COMMIT_SHA:12:5}
echo $PWD

echo ">>> git reset --hard HEAD  <<< Reseting possible local changes"
git reset --hard HEAD

echo ">>> git pull origin $(git rev-parse --abbrev-ref HEAD)  <<< Pulling master into current branch"
git pull origin $(git rev-parse --abbrev-ref HEAD) || true
#git pull "origin" +refs/heads/master

export DESTINATION_PATH=/var/www/html/moto_snapshot/${CID}/
echo ${DESTINATION_PATH}

mkdir ${DESTINATION_PATH} || true
sudo rm -rf ${DESTINATION_PATH}*
sudo cp -r . ${DESTINATION_PATH}	
sudo chown -R www-data:www-data ${DESTINATION_PATH}

set -e

if php --version | head -1 | grep -q 'PHP 7' ; then
  php7=1
else
  php7=
fi
cd ${DESTINATION_PATH}
# All image files must have lower-case extensions
if [ "`find . -iname '*.jpg' -o -iname '*.png' 2>/dev/null | grep -v "\.png" | grep -v "\.jpg" | grep -v ow_userfiles | grep -v ow_pluginfiles | grep -v ow_static | wc -l`" != "0" ]; then
  echo "Error: Image files must have lower-case extensions."
  exit 1
fi

db_host='localhost'
db_port=3306
db_name=oxwalltest${CID}
web_host=http://213.233.177.156/${CID}/

echo "---------------------------------------------------------------------"
echo "URL: ${web_host}  <--------------------------"
echo "---------------------------------------------------------------------"

echo "`date -Iseconds` Create database..."
sudo mysql -u root --password='Mot00_Te$#taki12' -e "DROP DATABASE IF EXISTS ${db_name};";
sudo mysql -u root --password='Mot00_Te$#taki12' -e "CREATE DATABASE ${db_name};";
sudo mysql -u root --password='Mot00_Te$#taki12' -e "GRANT ALL PRIVILEGES ON ${db_name}.* TO 'oxwall-test'@'localhost'; FLUSH PRIVILEGES;";
echo "`date -Iseconds` Clearing database..."
#mysql -h ${db_host} -P ${db_port} --user=oxwall-test --password=oxTest123 --execute='call drop_all_tables();' $db_name

echo "`date -Iseconds` Creating a directory to hold snapshots of failed tests..."
export SNAPSHOT_DIR="/var/www/html/moto_testfiles/${CID}/"
sudo rm -rf "${SNAPSHOT_DIR}"
sudo mkdir "${SNAPSHOT_DIR}"
sudo chmod g+w "${SNAPSHOT_DIR}"

# Apache root directory is a symbolic link to the git root. So no copying is needed
echo "`date -Iseconds` Providing access for Apache2 WebServer..."
sudo rm -f ${DESTINATION_PATH}ow_log/* || true
sudo rm -f ${DESTINATION_PATH}ow_includes/config.php || true
sudo cp ${DESTINATION_PATH}ow_includes/config.php.default ${DESTINATION_PATH}ow_includes/config.php

# touch ow_includes/config.php
sudo mkdir -p ${DESTINATION_PATH}ow_pluginfiles/admin/languages/import/
sudo touch ${DESTINATION_PATH}ow_pluginfiles/admin/languages/import/index.html

echo ">>> change permissions"
find . -user gitlab-runner -exec chmod g+w {} + || true
find . -user gitlab-runner -exec chown www-data:gitlab-runner {} + || true


echo "`date -Iseconds` Updating with composer..."
if [ $php7 ]; then
  sudo php ${DESTINATION_PATH}composer.phar update
  sudo git checkout -- ${DESTINATION_PATH}composer.lock
else
  sudo php ${DESTINATION_PATH}composer.phar install
fi

sudo chmod u+x ${DESTINATION_PATH}ow_libraries/vendor/bin/phpunit

sudo chown -R www-data:www-data ${DESTINATION_PATH}
