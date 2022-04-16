#!/bin/bash

##############
# migrate.sh #
##############
#
# Description: 
# Updates URL in database so the site can run on localhost
#

mysql -uroot -p$MYSQL_ROOT_PASSWORD -D$MYSQL_DATABASE -e "
UPDATE ${WORDPRESS_TABLE_PREFIX}options SET option_value = REPLACE(option_value, '${PRODUCTION_URL}', '${DEV_URL}') WHERE option_name = 'home' OR option_name = 'siteurl'; 
UPDATE ${WORDPRESS_TABLE_PREFIX}posts SET guid = REPLACE(guid, '${PRODUCTION_URL}', '${DEV_URL}'); 
UPDATE ${WORDPRESS_TABLE_PREFIX}posts SET post_content = REPLACE(post_content, '${PRODUCTION_URL}', '${DEV_URL}'); 
UPDATE ${WORDPRESS_TABLE_PREFIX}posts SET post_content = REPLACE(post_content, 'src=\"${PRODUCTION_URL}\"', 'src=\"${DEV_URL}\"'); 
UPDATE ${WORDPRESS_TABLE_PREFIX}posts SET guid = REPLACE(guid, '${PRODUCTION_URL}', '${DEV_URL}') WHERE post_type = 'attachment'; 
UPDATE ${WORDPRESS_TABLE_PREFIX}postmeta SET meta_value = REPLACE(meta_value, '${PRODUCTION_URL}', '${DEV_URL}');
UPDATE ${WORDPRESS_TABLE_PREFIX}snippets SET code = REPLACE(code, 'src=\"PROD_ADMIN_URL\"', 'src=\"${DEV_URL}\"');"
