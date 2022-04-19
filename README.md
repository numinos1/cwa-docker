# Bootstrap The Application

## 1. Install the Docker Desktop

https://docs.docker.com/get-docker/


## 2. Create Keys and add to remote server

```bash
$cd ~/.ssh
$ssh-keygen -t rsa
  (name = id_cwops)
$cat id_cwops.pub
  (copy the key)
```

- Login to the cPanel
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
$ssh cwa
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

```bash
$ssh cwa
$mysqldump -u cwacwops_wp540 cwacwops_wp540 -p --no-tablespaces | gzip > backup.sql.gz
$tar -cvzf wp-content.tar.gz www/wp-content
$exit
$scp cwa:backup.sql.gz init/backup.sql.gz
$scp cwa:wp-content.tar.gz .
$tar xvfz wp-content.tar.gz
$rm wp-content.tar.gz
```

## 8. Bootstrap the Docker Image

```bash
$docker-compose up -d 
$docker-compose exec wordpress prep.sh
```

# Updating the database and wp-content

## 1. Stop & Throw away the Docker Image

- In the Docker Desktop, click "Stop"
- In the Docker Desktop, click "Trash"

## 2. Remove the existing db and wp-content files

```bash
$rm -rf mysql
$rm -rf www
$rm init/backup.sql.gz
```

## 3. Re-Download and Bootstrap Docker

- Follow steps 7 & 8 In the last section

# Using the Application

## WordPress Admin and Program List

- http://localhost:3073/wp-login.php
- http://localhost:3073/program-list/

## Access MySQL Through Docker CLI

```bash
$docker ps
$docker exec -it <image-id> bash
$mysql -u root -p
```

## Access MySQL Through Local CLI

```bash
$mysql -h 127.0.0.1 -P 3074 -u cwacwops_wp540 --password="cwacwops" cwacwops_wp540
$mysql -h 127.0.0.1 -p 3074 -u root --password=cwacwops
```

## Node Utilites to Play With

```bash
$cd utils
$yarn
$node snippets.mjs
$node tables.mjs
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

## MySQL Tables

cwa_advisorclass                         
cwa_advisorclass2                        
cwa_advisornew                           
cwa_advisornew2                          
cwa_audio_assessment                     
cwa_audio_assessment2                    
cwa_evaluate_advisor                     
cwa_evaluate_advisor2                    
cwa_past_advisorclass                    
cwa_past_advisorclass2                   
cwa_past_advisornew                      
cwa_past_advisornew2                     
cwa_past_student                         
cwa_past_student2                        
cwa_production_email                     
cwa_reports                              
cwa_reports2                             
cwa_student                              
cwa_student2                             
cwa_student_fields                       
cwa_testmode_email                       
wpw1_actionscheduler_actions             
wpw1_actionscheduler_claims              
wpw1_actionscheduler_groups              
wpw1_actionscheduler_logs                
wpw1_aft_cc                              
wpw1_commentmeta                         
wpw1_comments                            
wpw1_links                               
wpw1_options                             
wpw1_postmeta                            
wpw1_posts                               
wpw1_simple_history                      
wpw1_simple_history_contexts             
wpw1_snippets                            
wpw1_term_relationships                  
wpw1_term_taxonomy                       
wpw1_termmeta                            
wpw1_terms                               
wpw1_usermeta                            
wpw1_users                               
wpw1_wp_phpmyadmin_extension__errors_log 
wpw1_wpmailsmtp_debug_events             
wpw1_wpmailsmtp_tasks_meta      

# cwa_ Migration Commands

RENAME TABLE `cwa_advisorclass` TO `wpw1_cwa_advisorclass`;
RENAME TABLE `cwa_advisorclass2` TO `wpw1_cwa_advisorclass2`;
RENAME TABLE `cwa_advisornew` TO `wpw1_cwa_advisornew`;
RENAME TABLE `cwa_advisornew2` TO `wpw1_cwa_advisornew2`;
RENAME TABLE `cwa_audio_assessment` TO `wpw1_cwa_audio_assessment`;
RENAME TABLE `cwa_audio_assessment2` TO `wpw1_cwa_audio_assessment2`;
RENAME TABLE `cwa_evaluate_advisor` TO `wpw1_cwa_evaluate_advisor`;
RENAME TABLE `cwa_evaluate_advisor2` TO `wpw1_cwa_evaluate_advisor2`;
RENAME TABLE `cwa_past_advisorclass` TO `wpw1_cwa_past_advisorclass`;
RENAME TABLE `cwa_past_advisorclass2` TO `wpw1_cwa_past_advisorclass2`;
RENAME TABLE `cwa_past_advisornew` TO `wpw1_cwa_past_advisornew`;
RENAME TABLE `cwa_past_advisornew2` TO `wpw1_cwa_past_advisornew2`;
RENAME TABLE `cwa_past_student` TO `wpw1_cwa_past_student`;
RENAME TABLE `cwa_past_student2` TO `wpw1_cwa_past_student2`;
RENAME TABLE `cwa_production_email` TO `wpw1_cwa_production_email`;
RENAME TABLE `cwa_reports` TO `wpw1_cwa_reports`;
RENAME TABLE `cwa_reports2` TO `wpw1_cwa_reports2`;
RENAME TABLE `cwa_student` TO `wpw1_cwa_student`;
RENAME TABLE `cwa_student2` TO `wpw1_cwa_student2`;
RENAME TABLE `cwa_student_fields` TO `wpw1_cwa_student_fields`;
RENAME TABLE `cwa_testmode_email` TO `wpw1_cwa_testmode_email`;
UPDATE wpw1_snippets SET code = REPLACE(code, 'cwa_', 'wpw1_cwa_');

UPDATE wpw1_snippets SET code = REPLACE(code, '/home/cwopsorg/CWAT', '/Users/abunker/Work/wordpress/CWAT');
UPDATE wpw1_snippets SET code = REPLACE(code, '/home/cwacwops/CWAT', '/Users/abunker/Work/wordpress/CWAT');