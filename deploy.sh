#!/bin/bash
if [ $# != 1  ];then
    echo "usage: $0 {tag/branch}" 
    exit
fi

git fetch -p
git checkout $1 -f
composer install --prefer-dist -v
chmod 777 -R ./
php app/console doctrine:cache:clear-metadata
php app/console doctrine:cache:clear-query
php app/console doctrine:cache:clear-result
php app/console doctrine:schema:update --force
php app/console assetic:dump

# opcache缓存清空
curl http://127.0.0.1:8999
