#!/bin/bash

SCRIPT_DIR=$(cd $(dirname $0);pwd)
echo $SCRIPT_DIR

php ${SCRIPT_DIR}/mastodon_post_yahooweather.php
php ${SCRIPT_DIR}/mastodon_post_zemi.php today
