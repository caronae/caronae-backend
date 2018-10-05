#!/bin/bash
set -euo pipefail

SRC_DIR=$(dirname "${BASH_SOURCE[0]}")

if [ -f "$SRC_DIR/../build_number.txt" ]; then
    version=$(cat "$SRC_DIR/../build_number.txt")
else
    version="latest"
fi
echo "Version: $version"

tag=${1:-latest}
echo "Tag: $tag"

docker_push=${DOCKER_PUSH:-false}
echo "Docker push: $docker_push"

docker pull caronae/backend:${version}
docker tag caronae/backend:${version} caronae/backend:${tag}

if [ "$docker_push" == true ]; then
  docker login -u ${DOCKER_USER} -p ${DOCKER_PASS}
  docker push caronae/backend:${tag}
fi
