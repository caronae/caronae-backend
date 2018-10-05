#!/bin/sh
set -euo pipefail

docker-compose -f docker/docker-compose.test.yml up --build --exit-code-from caronae-backend-tests
