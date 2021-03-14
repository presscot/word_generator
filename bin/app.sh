#!/usr/bin/env bash

CONTAINER=${1}
CURRENT=$( cd "$(dirname "$0")" ; pwd -P )
COMMAND=${@:2}

while [[ ! $(docker ps -f name="${CONTAINER}" -q) ]] ; do
    ((c++)) && ((c==120)) && { echo "Error!" 1>&2 && exit 2;}
    sleep 0.5
done

if [[ "${COMMAND}" =~ ^ME[[:blank:]]*\=\>[[:blank:]]* ]]; then
    ID=$(id -u)
    USER=$(id -u -n)
    COMMAND=$(echo "${COMMAND}" | sed "s/^${BASH_REMATCH[0]}//")
    COMMAND="/in_container/resolver.sh \"$COMMAND\""
    WWW_DATA="-u 0:0 -e USER_ID_ENV=${ID} -e USER_ENV=${USER}"
else
    WWW_DATA="-u 82:82"
fi

test -t 1 && TTY="-ti"
docker exec ${WWW_DATA} ${TTY} $(docker ps --filter="name=${CONTAINER}" -q) sh -c "${COMMAND}" ; exit $?
