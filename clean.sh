CONTAINER=$(./bin/get-container-name.sh 'fpm')

docker stop "${CONTAINER}"
docker rm "${CONTAINER}"
docker rmi "${CONTAINER}" -f
