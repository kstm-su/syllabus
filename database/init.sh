#!/bin/sh
cd `dirname "$0"`

if test $1 = '' ; then
	echo "useage: `basename $0` 'db_user' 'db_password'"
	exit
fi

read -sp 'Enter MySQL password: ' PASSWORD
echo "\n"
ADMIN_USER=`php -r "require_once('../lib/config.php');echo ADMIN_USER;"`
ADMIN_PASSWD=`php -r "require_once('../lib/config.php');echo ADMIN_PASSWD;"`
GUEST_USER=`php -r "require_once('../lib/config.php');echo GUEST_USER;"`
GUEST_PASSWD=`php -r "require_once('../lib/config.php');echo GUEST_PASSWD;"`
DB_NAME=`php -r "require_once('../lib/config.php');echo DB_NAME;"`


mysql -u$1 -p$PASSWORD -e"CREATE DATABASE \`$DB_NAME\`" > /dev/null 2>&1
mysql -u$1 -p$PASSWORD $DB_NAME -e"CREATE USER \`$ADMIN_USER\` IDENTIFIED BY PASSWORD '$ADMIN_PASSWORD'" > /dev/null 2>&1
mysql -u$1 -p$PASSWORD $DB_NAME -e"CREATE USER \`$GUEST_USER\` IDENTIFIED BY PASSWORD '$GUEST_PASSWORD'" > /dev/null 2>&1
mysql -u$1 -p$PASSWORD $DB_NAME -e"GRANT ALL ON $DB_NAME.* FOR \`$ADMIN_USER\`" > /dev/null 2>&1
mysql -u$1 -p$PASSWORD $DB_NAME -e"GRANT SELECT ON $DB_NAME.* FOR \`$GUEST_USER\`" > /dev/null 2>&1
