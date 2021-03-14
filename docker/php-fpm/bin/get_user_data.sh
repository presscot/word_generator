#!/usr/bin/env bash

COMMAND="${@}"

if [[ "${COMMAND}" =~ ^USER[[:blank:]]([a-z0-9]+)[[:blank:]]+([0-9]+)[[:blank:]]*\;[[:blank:]]* ]]; then
    USER=${BASH_REMATCH[1]}
    USER_ID=${BASH_REMATCH[2]}
    COMMAND=$(echo "${COMMAND}" | sed "s/^${BASH_REMATCH[0]}//")
else
    DEF="user${USER_ID}"
    USER="${USER_ENV:-$DEF}"
    USER_ID="${USER_ID_ENV:-0}"
fi

echo "${USER} ${USER_ID} ${COMMAND}"
