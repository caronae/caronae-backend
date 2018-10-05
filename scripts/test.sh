#!/bin/sh
set -euo pipefail

SRC_DIR=$(dirname "${BASH_SOURCE[0]}")

docker-compose -f ${SRC_DIR}/../docker/docker-compose.test.yml up --build --exit-code-from caronae-backend-tests
