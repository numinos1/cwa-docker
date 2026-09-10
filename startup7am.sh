#!/bin/bash

echo "retrieving backup.sql.gz"
scp cwa:backup7am.sql.gz init/backup.sql.gz
echo "retrieving wp-content.tar.tz"
scp cwa:wp-content7am.tar.gz .
echo "decompressing wp-content.tar.gz"
tar xvfz wp-content7am.tar.gz
rm wp-content7am.tar.gz
echo "starting Docker"
docker-compose up -d
docker-compose exec wordpress prep.sh
