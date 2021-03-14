#!/usr/bin/env bash

DATA="${@:2}"
I=${1}

IFS=' ' read -ra PA <<< "$DATA"

if [[ ${I:0:1} == "+" ]] ; then
    echo ${PA[@]:$((${I}))}
else
    echo ${PA[${I}]}
fi