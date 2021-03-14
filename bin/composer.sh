#!/usr/bin/env bash

CURRENT=$( cd "$(dirname "$0")" ; pwd -P )
CONTAINER=$(${CURRENT}/get-container-name.sh "fpm")
COMMAND="ME => php -d memory_limit=-1 /usr/local/bin/composer ${@}"

${CURRENT}/app.sh ${CONTAINER} "${COMMAND}" ; exit $?
