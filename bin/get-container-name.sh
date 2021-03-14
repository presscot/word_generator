#!/usr/bin/env bash

ALIAS=${1}

CURRENT=$( cd "$(dirname "$0")" ; pwd -P )
PROJECT_NAME=$("${CURRENT}/project-name.sh")

echo "${PROJECT_NAME}_${ALIAS}"
