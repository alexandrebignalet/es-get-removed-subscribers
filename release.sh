#!/bin/bash

git config --global push.default matching
git remote add deploy ssh://git@$SERVER_IP_ADDRESS:$DEPLOY_DIR
git push deploy master

ssh git@$SERVER_IP_ADDRESS <<EOF
  cd $DEPLOY_DIR
  docker-compose build
  docker-compose up --no-deps -d
EOF