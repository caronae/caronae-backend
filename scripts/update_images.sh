#!/bin/sh
set -eo pipefail

if [ $# -eq 0 ]; then
    echo "TAG is not defined. Usage: ./update_images.sh <TAG>"
    exit 1
fi

sudo CARONAE_ENV_TAG=$1 su

echo "Deploying with tag: $CARONAE_ENV_TAG"

cd /var/caronae/caronae-docker

git fetch origin master
git reset --hard origin/master

docker pull caronae/backend:$CARONAE_ENV_TAG
docker pull caronae/backend-worker:$CARONAE_ENV_TAG

/usr/local/bin/docker-compose -f docker-compose.yml -f docker-compose.prod.yml up -d

docker exec -it caronae-backend /var/www/scripts/update_laravel.sh