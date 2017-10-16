#!/bin/bash
cd `dirname "$0"`

read -sp 'Enter MySQL root password: ' PASSWORD
echo ''

if [ ! -e ../lib/config.php ]; then
	cp ../lib/config.sample.php ../lib/config.php
fi

ADMIN_USER=`php -r "require_once('../lib/config.php');echo ADMIN_USER;"`
ADMIN_PASSWD=`php -r "require_once('../lib/config.php');echo ADMIN_PASSWD;"`
GUEST_USER=`php -r "require_once('../lib/config.php');echo GUEST_USER;"`
GUEST_PASSWD=`php -r "require_once('../lib/config.php');echo GUEST_PASSWD;"`
DB_NAME=`php -r "require_once('../lib/config.php');echo DB_NAME;"`

mysql -uroot -p$PASSWORD -e"CREATE DATABASE $DB_NAME" > /dev/null 2>&1
mysql -uroot -p$PASSWORD $DB_NAME -e"CREATE USER $ADMIN_USER IDENTIFIED BY '$ADMIN_PASSWD'" > /dev/null 2>&1
mysql -uroot -p$PASSWORD $DB_NAME -e"CREATE USER $GUEST_USER IDENTIFIED BY '$GUEST_PASSWD'" > /dev/null 2>&1
mysql -uroot -p$PASSWORD $DB_NAME -e"GRANT ALL ON $DB_NAME.* TO $ADMIN_USER" > /dev/null 2>&1
mysql -uroot -p$PASSWORD $DB_NAME -e"GRANT SELECT ON $DB_NAME.* TO $GUEST_USER" > /dev/null 2>&1
mysql -uroot -p$PASSWORD $DB_NAME < tables.sql > /dev/null 2>%1

echo 'Next step: update.sh'
