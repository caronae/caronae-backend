#!/bin/sh
set -eo pipefail

if [ $# -eq 0 ]; then
    echo "TAG is not defined. Usage: ./update_images.sh <TAG>"
    exit 1
fi

sudo CARONAE_ENV_TAG=$1 su
set -eo pipefail

echo "Updating caronae-docker..."
cd /var/caronae/caronae-docker
git fetch origin master
git reset --hard origin/master

echo "Updating images using the tag $CARONAE_ENV_TAG"
/usr/local/bin/docker-compose -f docker-compose.yml -f docker-compose.prod.yml pull caronae-backend caronae-backend-worker caronae-backend-task-scheduler
/usr/local/bin/docker-compose -f docker-compose.yml -f docker-compose.prod.yml stop caronae-backend caronae-backend-worker caronae-backend-task-scheduler
/usr/local/bin/docker-compose -f docker-compose.yml -f docker-compose.prod.yml rm -f caronae-backend caronae-backend-worker caronae-backend-task-scheduler
/usr/local/bin/docker-compose -f docker-compose.yml -f docker-compose.prod.yml up -d

echo "Running backend update scripts..."
docker exec caronae-backend sh /var/www/scripts/update_laravel.sh

echo "Clean-up unused Docker images and volumes..."
docker image prune -af
docker volume prune -f
