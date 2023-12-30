# Bootstrap The Application

## 1. Install the Docker Desktop

https://docs.docker.com/get-docker/


## 2. Create Keys and add to remote server

```bash
cd ~/.ssh
ssh-keygen -t rsa
  (name = id_cwops)
cat id_cwops.pub
  (copy the key)
```

- Login to the cPanel		https://cwa.cwops.org/cpanel/
- Click on SSH Access
- Click on Import Key
- Give it a name
- Paste in the public key
- Don't enter a password
- Don't enter a private key
- On the main SSH Access page, click the key's "Manage" link
- Click the "Authorize" button
- You should now be able to ssh without a password

```bash
ssh cwa
```

## 3. Add Keys to ~/.ssh/config

```conf
Host cwa
  HostName cwa.cwops.org
  User cwacwops
  IdentityFile ~/.ssh/id_cwops
```

## 4. Download the cwa-cwops repo

```bash
git clone git@github.com:numinos1/cwa-docker.git
```

## 5. Create the .env config file

```conf
production_url=https://cwops.org
prod_admin_url=https://cwa.cwops.org
dev_url=http://localhost:3073

db_table_prefix=wpw1_
wp_plugins_to_disable=

db_host=db:3306
db_user=cwacwops_wp540
db_password=cwacwops
db_name=cwacwops_wp540
db_root_password=cwacwops
wp_debug_mode=false
```

## 6. Modification for M1 Macs

Add the following line to both the "wordpress" and "db" sections after the "image"

```yml
platform: linux/x86_64
```

## 7. Download the db and wp-content

- Note: The DB password is in www/wp-config.php

```bash
ssh cwa
mysqldump -u cwacwops_wp540 cwacwops_wp540 -p --no-tablespaces | gzip > backup.sql.gz
tar -cvzf wp-content.tar.gz www/wp-content
exit
scp cwa:backup.sql.gz init/backup.sql.gz
scp cwa:wp-content.tar.gz .
tar xvfz wp-content.tar.gz
rm wp-content.tar.gz
```

## 8. Bootstrap the Docker Image

```bash
docker-compose up -d 
docker-compose exec wordpress prep.sh
```

# Updating the database and wp-content

## 1. Stop & Throw away the Docker Image

- In the Docker Desktop, click "Stop"
- In the Docker Desktop, click "Trash"

## 2. Remove the existing db and wp-content files

```bash
rm -rf mysql
rm -rf www
rm init/backup.sql.gz
```

## 3. Re-Download and Bootstrap Docker

- Follow steps 7 & 8 In the last section

# Using the Application

## WordPress Admin and Program List

- http://localhost:3073/wp-login.php
- http://localhost:3073/program-list/

* Note: Wordpress uses personal login credentials

## Access MySQL Through Docker CLI

```bash
docker ps
docker exec -it <image-id> bash
mysql -u root -p
```

## Access MySQL Through Local CLI

```bash
mysql -h 127.0.0.1 -P 3074 -u cwacwops_wp540 --password="cwacwops" cwacwops_wp540
mysql -h 127.0.0.1 -p 3074 -u root --password=cwacwops
```

## Node Utilites to Play With

```bash
cd utils
yarn
node snippets.mjs
node tables.mjs
```

# Documentation

## Directory Structure

| Directory            | Description         |
| -------------------- | ------------------- |
| __/mysql__           | Where Docker will mount the MySQL database files |
| __/docs__            | Markdown documentation files |
| __/init__            | Shell scripts for initialization |
| __/utils__           | Node scripts |
| __/www/wp-content__  | Where Docker will mount the wp-content Wordpress directory |


