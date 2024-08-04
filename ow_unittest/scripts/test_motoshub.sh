CID=${CI_COMMIT_SHA:12:5}
web_host=http://213.233.177.156/${CID}/
echo "---------------------------------------------------------------------"
echo "URL: ${web_host}  <--------------------------"
echo "---------------------------------------------------------------------"

set -e
echo $PWD

export DESTINATION_PATH=/var/www/html/moto_snapshot/${CID}/
echo ${DESTINATION_PATH}

pwd1=`pwd`
cd ${DESTINATION_PATH}
sudo sed -i -e "s/localhost/213.233.177.156\/${CID}/g" ow_unittest/codeception/tests/acceptance.suite.yml
sudo sed -i -e "s/localhost/213.233.177.156\/${CID}/g" ow_unittest/codeception/tests/api.suite.yml
sudo sh -c "echo \"<?php define('db_name', 'oxwalltest${CID}');\" > ow_unittest/codeception/Settings.php"

sudo chown -R www-data:www-data ${DESTINATION_PATH}

echo "`date -Iseconds` Running codeception tests----------------------------------------------------------------"
cd ${DESTINATION_PATH}ow_unittest/codeception/
sudo php ${DESTINATION_PATH}ow_libraries/vendor/bin/codecept run #--steps --xml --html

echo "`date -Iseconds` Running phpunit+selenium tests----------------------------------------------------------------"
sudo chown -R www-data:www-data ${DESTINATION_PATH}

find . -user gitlab-runner -exec chmod g+w {} + || true
sudo chmod g+w ${DESTINATION_PATH}ow_includes/config.php
sudo cat >> ${DESTINATION_PATH}ow_includes/config.php <<EOF
ini_set('session.save_handler', 'memcached');
ini_set('session.save_path', 'localhost:11211');
EOF

sudo ${DESTINATION_PATH}ow_libraries/vendor/bin/phpunit --stderr --configuration ${DESTINATION_PATH}ow_unittest/phpunit.xml --log-junit ${DESTINATION_PATH}ow_log/phpunit-result.xml --verbose

sudo chown -R gitlab-runner:www-data ${pwd1}/ow_unittest
#rm -R ${DESTINATION_PATH}
#sudo mysql -u root --password='Mot00_Te$#taki12' -e "DROP DATABASE ${db_name};";