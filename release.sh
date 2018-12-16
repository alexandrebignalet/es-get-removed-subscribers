#!/bin/bash

ssh git@${SERVER_IP_ADDRESS} <<EOF
  git clone git@github.com:alexandrebignalet/es-get-removed-subscribers.git
  docker-compose build
  docker-compose up --no-deps -d
EOF