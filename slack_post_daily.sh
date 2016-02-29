#!/bin/bash

SCRIPT_DIR=$(cd $(dirname $0);pwd)
echo $SCRIPT_DIR

php ${SCRIPT_DIR}/slack_post_weather.php
php ${SCRIPT_DIR}/slack_post_b4.php
php ${SCRIPT_DIR}/slack_post_boss.php
