#!/usr/bin/env bash

USER="${1}"
USER_ID="${2}"
COMMAND="${@:3}"
ID=$(id -u)

if [[ $ID -eq 0 ]]; then
#500 instead of 1000 for mac os users
    if [[ ${USER_ID} -ge 500 ]]; then
        $(id -u ${USER_ID} > /dev/null 2>&1 ) || $(id -u ${USER} > /dev/null 2>&1 ) && EXIST=1

        if [[ -z ${EXIST} ]]; then
            SSH_DIR=${SSH_DIR:-/credentials}

            addgroup -S -g ${USER_ID} ${USER} && \
            adduser -S -D -s /bin/false -u ${USER_ID} -G ${USER} ${USER} && \
            sed -e "s/^wheel:\(.*\)/wheel:\1,${USER}/g" -i /etc/group && \
            ln -s /credentials /home/${USER}/.ssh
        fi

        sudo -E -u ${USER} -g ${USER} sh -c "${COMMAND}"; exit $?
    fi

    if [[ ${USER_ID} -gt 0 ]]; then
        echo "You can not create and login to account with id less that 500" 1>&2;
        exit 127
    fi
fi

/bin/bash -l -c "${COMMAND}"
