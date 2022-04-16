# Install and Bootstrap

## 1. Download the cwa-cwops repo

```bash
git clone ...
```

## 2. Install Docker

- Install the Docker Desktop

## 3. Create Keys and add to remote server

...TODO...

## 4. Add the CWA Keys to ~/.ssh/config

```conf
Host cwa
  HostName cwa.cwops.org
  User cwacwops
  IdentityFile ~/.ssh/id_cwops
```

## 5. Add .env config file

```conf
production_url=https://cwops.org
db_table_prefix=wpw1_
wp_plugins_to_disable=

db_host=db:3306
db_user=cwacwops_wp540
db_password=PASSWORD_GOES_HERE
db_name=cwacwops_wp540
db_root_password=cwa
wp_debug_mode=false
```

# Download the db and wp-content

```bash
ssh cwa
mysqldump -u cwacwops_wp540 cwacwops_wp540 -p | gzip > backup.sql.gz
tar -cvzf wp-content.tar.gz www/wp-content
exit
scp cwa:backup.sql.gz mysqldumps/backup.sql.gz
rm -rf wp-content
scp cwa:wp-content.tar.gz .
tar xvfz wp-content.tar.gz
mv www/wp-content .
rmdir www
rm wp-content.tar.gz
```

# Bootstrap the Docker Image

```bash
docker-compose up -d && docker-compose exec wordpress prep.sh
```

# Run the node scripts

```bash
cd utils
yarn
node snippets.mjs
node tables.mjs
```

# Access the Reports

1. http://localhost:3073/wp-login.php
2. Login with username and password
3. http://localhost:3073/program-list/

# MySQL Docker CLI

```bash
docker ps
docker exec -it <image-id> bash
mysql -u root -p
```

# MySQL Local CLI

```bash
mysql -h 127.0.0.1 -P 3074 -u cwacwops_wp540 --password="7B-m)p7d2S" cwacwops_wp540
mysql -h 127.0.0.1 -p 3074 -u root --password=cwaroot
```

# Directory Structure

/database       Where Docker will mount the MySQL database files
/docs           Markdown documentation files
/init           Shell scripts for initialization
/migration      Node scripts
/mysqldumps     Where Docker will mount the backup.tar.gz database dump
/wp-content     Where Docker will mount the wp-content Wordpress directory
