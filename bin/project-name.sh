#!/usr/bin/env bash

CURRENT=$( cd "$(dirname "$0")" ; pwd -P )
CURRENT=$(dirname ${CURRENT})

echo ${CURRENT##*/}
