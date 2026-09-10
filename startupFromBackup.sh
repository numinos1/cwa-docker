#!/bin/bash

# echo "retrieving backup.sql.gz"
 scp cwa:backups/backups-2025-11-15/backup.sql.gz init/backup.sql.gz

echo "retrieving jetpack-waf.tar.gz"
scp cwa:backups/backups-2025-11-15/jetpack-waf.tar.gz .
echo "decompressing jetpack-waf.tar.gz"
tar xvfz jetpack-waf.tar.gz --strip-components=2

echo "retrieving languages.tar.gz"
scp cwa:backups/backups-2025-11-15/languages.tar.gz .
echo "decompressing languages.tar.gz"
tar xvfz languages.tar.gz --strip-components=2

echo "retrieving plugins.tar.gz"
scp cwa:backups/backups-2025-11-15/plugins.tar.gz .
echo "decompressing plugins.tar.gz"
tar xvfz plugins.tar.gz --strip-components=2

echo "retrieving themes.tar.gz"
scp cwa:backups/backups-2025-11-15/themes.tar.gz .
echo "decompressing themes.tar.gz"
tar xvfz themes.tar.gz --strip-components=2

echo "retrieving updraft.tar.gz"
scp cwa:backups/backups-2025-11-15/updraft.tar.gz .
echo "decompressing updraft.tar.gz"
tar xvfz updraft.tar.gz --strip-components=2

echo "retrieving upgrade-temp-backup.tar.gz"
scp cwa:backups/backups-2025-11-15/upgrade-temp-backup.tar.gz .
echo "decompressing upgrade-temp-backup.tar.gz"
tar xvfz upgrade-temp-backup.tar.gz --strip-components=2

echo "retrieving upgrade.tar.gz"
scp cwa:backups/backups-2025-11-15/upgrade.tar.gz .
echo "decompressing upgrade.tar.gz"
tar xvfz upgrade.tar.gz --strip-components=2

echo "retrieving uploads.tar.gz"
scp cwa:backups/backups-2025-11-15/uploads.tar.gz .
echo "decompressing uploads.tar.gz"
tar xvfz uploads.tar.gz --strip-components=2

echo "starting Docker"
docker-compose up -d
docker-compose exec wordpress prep.sh
