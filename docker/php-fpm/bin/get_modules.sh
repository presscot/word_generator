#!/usr/bin/env bash

DIRS=()
URLS=()
BRANCHES=()

CURRENT_DIR=${1:-"$(pwd)"}

if [ ! -f "${CURRENT_DIR}/.gitmodules" ];
then
    exit 0
fi

while read LINE; do
    if [[ $LINE =~ ^\[submodule[[:blank:]]\"(.*)\"\]$ ]]; then
        DIR=${BASH_REMATCH[1]}
        DIRS+=("${DIR}")
        URLS+=("")
        BRANCHES+=("master")
    fi

    if [[ $LINE =~ ^[[:blank:]]*path[[:blank:]]*\=[[:blank:]]*(.*)$ ]]; then
        DIR=${BASH_REMATCH[1]}
        DIRS[-1]="${DIR}"
    fi

    if [[ $LINE =~ ^[[:blank:]]*url[[:blank:]]*\=[[:blank:]]*(.*)$ ]]; then
        URL=${BASH_REMATCH[1]}
        URLS[-1]="${URL}"
    fi

    if [[ $LINE =~ ^[[:blank:]]*branch[[:blank:]]*\=[[:blank:]]*(.*)$ ]]; then
        BRANCH=${BASH_REMATCH[1]}
        BRANCHES[-1]="${BRANCH}"
    fi
done < "${CURRENT_DIR}/.gitmodules"

for i in "${!DIRS[@]}"
do
    DIR="${DIRS[$i]}"
    URL=$(echo "${URLS[$i]}" | sed -E -e 's/^(ssh|https):\/\/git-codecommit\..*\.amazonaws\.com\/v1\/repos\//codecommit:\/\//')
    BRANCH="${BRANCHES[$i]}"

    mkdir -p "${CURRENT_DIR}/${DIR}" && \
    git clone --depth 1 "${URL}" -b "${BRANCH}" "${CURRENT_DIR}/${DIR}" && \
    rm -rf "${CURRENT_DIR}/${DIR}/.git" && \
    $0 "${CURRENT_DIR}/${DIR}"
done
