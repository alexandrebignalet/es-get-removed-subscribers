#!/bin/bash

SERVICE_NAME="es-get-removed-subs"
APP_VERSION="${TRAVIS_BUILD_NUMBER:-local}"
DOCKER_IMAGE="${SERVICE_NAME}:${APP_VERSION}"

docker build -t "${DOCKER_USERNAME}"/"${DOCKER_IMAGE}" .

echo "${DOCKER_PASSWORD}" | docker login -u "${DOCKER_USERNAME}" --password-stdin
docker push "${DOCKER_USERNAME}"/"${DOCKER_IMAGE}"

ssh -T -i ./deploy_key git@"${SERVER_IP}" <<EOF
  echo "${DOCKER_PASSWORD}" | docker login -u "${DOCKER_USERNAME}" --password-stdin

  echo '>>> Pulling latest version'
  docker pull "${DOCKER_USERNAME}"/"${DOCKER_IMAGE}"

  echo '>>> Get old container id'
  CID="$(sudo docker ps | grep "${SERVICE_NAME}" | awk '{print $1}')"
  echo $CID

  echo '>>> Stopping old container'
  if [ "$CID" != "" ];
  then
    sudo docker stop $CID
  fi

  echo '>>> Recreate the container'
  docker run -d -e FTP_SERVER_HOST="${FTP_SERVER_HOST}" -e FTP_USERNAME="${FTP_USERNAME}" -e FTP_PASSWORD="${FTP_PASSWORD}" -e ACHETER_LOUER_KEY="${ACHETER_LOUER_KEY}" "${DOCKER_USERNAME}"/"${DOCKER_IMAGE}"

  echo '>>> Cleaning up containers'
  sudo docker ps -a | grep "Exit" | awk '{print $1}' | while read -r id ; do
    sudo docker rm $id
  done


  echo '>>> Cleaning up images'
  sudo docker images | grep "^<none>" | head -n 1 | awk 'BEGIN { FS = "[ \t]+" } { print $3 }'  | while read -r id ; do
    sudo docker rmi $id
  done
EOF