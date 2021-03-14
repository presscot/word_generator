
CONTAINER=$(./bin/get-container-name.sh 'fpm')

docker build -t ${CONTAINER}:latest ./docker/php-fpm/

docker run -d -v $(pwd):/var/www --rm --expose 8000 -p 8000:8000 --name "${CONTAINER}" "${CONTAINER}"

sh bin/composer.sh install -n --optimize-autoloader
