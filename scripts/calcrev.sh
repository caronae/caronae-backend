#!/bin/bash
set -eo pipefail

commit_count=$(git rev-list HEAD | wc -l)
prefix=""

branch_name=$(git rev-parse --abbrev-ref HEAD)
if [ "$branch_name" != "master" ]; then
    prefix="$branch_name-"
fi

version_name="$prefix$commit_count"
echo "$version_name"
