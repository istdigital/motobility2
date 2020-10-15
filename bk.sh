#!/bin/bash
cd /var/www/mobilizeme.com.au

SPATH='s3://ist-dbbackups/mobilizeme.com.au'
MYSQL_HOST='localhost'
MYSQL_USER='root'
MYSQL_PASSWORD='$Admin@123$'
DATABASE_NAME='mobilizeme'
TODAY=`date +"%d"`

if [ "$1" = "--db" ]; then
	sudo php7.2 bin/magento maintenance:enable

	sudo mysqldump -h ${MYSQL_HOST} \
	   -u ${MYSQL_USER} \
	   ${DATABASE_NAME} | gzip > ${DATABASE_NAME}-${TODAY}.sql.gz

	sudo php7.2 bin/magento maintenance:disable

	aws s3 cp ${DATABASE_NAME}-${TODAY}.sql.gz ${SPATH}/${DATABASE_NAME}-${TODAY}.sql.gz --storage-class ONEZONE_IA

	rm ${DATABASE_NAME}-${TODAY}.sql.gz
fi


if [ "$1" = "--media" ]; then
	aws s3 sync pub/media ${SPATH}/media  --storage-class ONEZONE_IA --exclude ".*" --exclude '*/cache/*' --exclude 'tmp/*' --exclude 'captcha/*' >> /dev/null
fi