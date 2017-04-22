#!/bin/bash -x

SCRIPT_DIR=$(cd $(dirname $0);pwd)
echo $SCRIPT_DIR

${SCRIPT_DIR}/mastodon_streaming_kill.sh
php ${SCRIPT_DIR}/mastodon_streaming.php
