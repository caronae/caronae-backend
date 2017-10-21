#!/bin/sh
set -eo pipefail

if [ $# -eq 0 ]; then
    echo "TAG is not defined. Usage: ./update_images.sh <TAG>"
    exit 1
fi

sudo CARONAE_ENV_TAG=$1 su

echo "Updating caronae-docker..."
cd /var/caronae/caronae-docker
git fetch origin master
git reset --hard origin/master

echo "Updating backend and backend-worker using the tag $CARONAE_ENV_TAG"
docker pull caronae/backend:$CARONAE_ENV_TAG
docker pull caronae/backend-worker:$CARONAE_ENV_TAG
/usr/local/bin/docker-compose -f docker-compose.yml -f docker-compose.prod.yml up -d

echo "Running backend update scripts..."
docker exec caronae-backend /var/www/scripts/update_laravel.sh