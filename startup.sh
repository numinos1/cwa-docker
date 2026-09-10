#!/bin/bash

# echo "retrieving backup.sql.gz"
 scp cwa:backup.sql.gz init/backup.sql.gz

echo "retrieving jetpack-waf.tar.gz"
scp cwa:jetpack-waf.tar.gz .
echo "decompressing jetpack-waf.tar.gz"
tar xvfz jetpack-waf.tar.gz

echo "retrieving languages.tar.gz"
scp cwa:languages.tar.gz .
echo "decompressing languages.tar.gz"
tar xvfz languages.tar.gz

echo "retrieving plugins.tar.gz"
scp cwa:plugins.tar.gz .
echo "decompressing plugins.tar.gz"
tar xvfz plugins.tar.gz

echo "retrieving themes.tar.gz"
scp cwa:themes.tar.gz .
echo "decompressing themes.tar.gz"
tar xvfz themes.tar.gz

echo "retrieving updraft.tar.gz"
scp cwa:updraft.tar.gz .
echo "decompressing updraft.tar.gz"
tar xvfz updraft.tar.gz

echo "retrieving upgrade-temp-backup.tar.gz"
scp cwa:upgrade-temp-backup.tar.gz .
echo "decompressing upgrade-temp-backup.tar.gz"
tar xvfz upgrade-temp-backup.tar.gz

echo "retrieving upgrade.tar.gz"
scp cwa:upgrade.tar.gz .
echo "decompressing upgrade.tar.gz"
tar xvfz upgrade.tar.gz

echo "retrieving uploads.tar.gz"
scp cwa:uploads.tar.gz .
echo "decompressing uploads.tar.gz"
tar xvfz uploads.tar.gz

echo "starting Docker"
docker-compose up -d
docker-compose exec wordpress prep.sh
