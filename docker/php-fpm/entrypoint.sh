#!/usr/bin/env bash

COMMAND="php-fpm"

DATA=$(/in_container/get_user_data.sh ${COMMAND})

USER=$(/in_container/split_data.sh 0 ${DATA})
USER_ID=$(/in_container/split_data.sh 1 ${DATA})
COMMAND=$(/in_container/split_data.sh "+2" ${DATA})

/in_container/handle_command.sh ${USER} ${USER_ID} "${COMMAND}"
