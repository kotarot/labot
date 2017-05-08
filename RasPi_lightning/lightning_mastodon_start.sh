#!/bin/bash -x

SCRIPT_DIR=$(cd $(dirname $0);pwd)
echo $SCRIPT_DIR

${SCRIPT_DIR}/lightning_mastodon_kill.sh
python ${SCRIPT_DIR}/lightning_mastodon.py
