#!/bin/bash

SCRIPT_DIR=$(cd $(dirname $0);pwd)
echo $SCRIPT_DIR

php ${SCRIPT_DIR}/hackey_weather.php
