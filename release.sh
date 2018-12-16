#!/bin/bash

ssh -T -i ./deploy_key git@${SERVER_IP_ADDRESS} <<EOF
  echo "Deleting existing project"
  rm -rf es-get-removed-subscribers/

  git clone https://github.com/alexandrebignalet/es-get-removed-subscribers.git

  echo "Getting in the cloned project"
  cd es-get-removed-subscribers/

  echo "Building and running the container"
  docker-compose build
  docker-compose up --no-deps -d
EOF