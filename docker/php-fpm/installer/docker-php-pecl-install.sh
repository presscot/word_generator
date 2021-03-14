#!/usr/bin/env bash

EXTS=()
OPTS=()
i=-1
TEMP_DIR=$(pecl config-get temp_dir)
EXT_DIR="/usr/src/php/ext"
INSTALLED=""

BUILD_OPT () {
    echo "BUILD_OPT"

    phpize && \
    ./configure ${1} && \
    make -j$(nproc) && \
    make install

    return $?
}

RETURN_ERROR () {
    echo "Trouble with install ${1}" 1>&2
    exit 127
}

mkdir -p ${TEMP_DIR}
mkdir -p /tmp/pear/cache

docker-php-source extract

for ARG in ${@}
do
    if ! echo "${ARG}" | grep -i -q "^\-\-[a-z]\+[a-z0-9\-]*\=\?[^ ]*$"
    then
        i=$(($i + 1))
        EXTS[$i]=${ARG}
        OPTS[$i]=""
    else
        OPTS[$i]="${OPTS[${i}]} ${ARG}"
    fi
done

for (( j=0; j<=$i; j++ ))
do
    EXT=${EXTS[${j}]}
    OPT=${OPTS[${j}]}

    mapfile -td \@ COM <<<"$EXT"
    COM=("${COM[@]%$'\n'}")
    EXT=${COM[0]}
    ALIAS=${COM[1]}
    SOURCE=${COM[2]}

    if [ ! ${ALIAS} ];
    then
        ALIAS=${EXT}
    fi

    if [ -d "${EXT_DIR}/${EXT}" ];
    then
        echo "INSTALL SOURCE EXTENSION"
        echo "${EXT}"

        docker-php-ext-install "${EXT}" || \
        docker-php-ext-install "${EXT}" || \
        RETURN_ERROR ${EXT}
    else
        if [ "${OPT}" == "" ];
        then
            echo "INSTALL PECL EXTENSION"
            echo "${EXT}"

            pecl install ${EXT} || \
            pecl install ${EXT} || \
            RETURN_ERROR ${EXT}
        else
            echo "INSTALL PECL EXTENSION WITH CUSTOM FLAGS"
            echo "${EXT} ${OPT}"

            if ! echo "${SOURCE}" | grep -i -q "^git=\?[^ ]*$"
            then
                echo "DOWNLOAD FROM PECL"
                pecl install --nobuild "${EXT}" || \
                pecl install --nobuild "${EXT}" || \
                RETURN_ERROR ${EXT}
            else
                echo "DOWNLOAD FROM GIT"
                VART=$(echo "${SOURCE}" | awk -F '=' '{print $2}')
                cd "${TEMP_DIR}"
                git clone "${VART}" || \
                git clone "${VART}" || \
                RETURN_ERROR ${EXT}
            fi
            cd "${TEMP_DIR}/${EXT}"
            BUILD_OPT ${OPT} || BUILD_OPT ${OPT} || RETURN_ERROR ${EXT}
            cd /tmp/
        fi
    fi

    INSTALLED="${INSTALLED} ${ALIAS}"
done
docker-php-ext-enable ${INSTALLED}
