APP="php ./bin/console"
COMMAND="${APP} ${@}"
CURRENT=$( cd "$(dirname "$0")" ; pwd -P )
CONTAINER=$(${CURRENT}/get-container-name.sh "fpm")

sh -c "${CURRENT}/app.sh ${CONTAINER} 'ME => ${COMMAND}'" ; exit $?
