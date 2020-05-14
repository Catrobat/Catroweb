#!/usr/bin/env bash

export BORG_RSH="ssh -i /root/.ssh/borg/borg_backup -F /root/.ssh/borg/borg_config"

BORG_REPO="backup@catrobat-backup:/mnt/md0/borg_backup/catrobat-share"
PUBLIC_FOLDER="/var/www/share/shared/public/resources"
SQL_RESTORE_FOLDER="/tmp/sqlrestore"
DEB_DEFAULT="/etc/mysql/debian.cnf"
SQL_BORG_FOLDER="borg_backup/tmp/catrobat-share"
BACKUP_ARCHIVE=$(/usr/local/bin/borg list backup@catrobat-backup:/mnt/md0/borg_backup/catrobat-share --last 1 | awk '{print $1;}')

echo ${BACKUP_ARCHIVE}

mkdir -p "${SQL_RESTORE_FOLDER}" &&

cd "${SQL_RESTORE_FOLDER}" &&
/usr/local/bin/borg extract --strip-components=3 ${BORG_REPO}::${BACKUP_ARCHIVE} ${SQL_BORG_FOLDER} &&
mysql --defaults-file=${DEB_DEFAULT} < mysqldump_all_databases.sql &&
rm mysqldump_all_databases.sql &&

cd "${PUBLIC_FOLDER}" &&
/usr/local/bin/borg extract --strip-components=6 ${BORG_REPO}::${BACKUP_ARCHIVE} ${PUBLIC_FOLDER:1}