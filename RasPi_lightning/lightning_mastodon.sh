#!/bin/bash -x

SCRIPT_DIR=$(cd $(dirname $0);pwd)
echo ${SCRIPT_DIR}
cd ${SCRIPT_DIR}

MASTODON_LIGHTNINGPROC=`ps aux | grep lightning_mastodon | grep python | grep -v grep | awk '{ print $2; }'`
if [ ! ${MASTODON_LIGHTNINGPROC} ]
then
    nohup ./lightning_mastodon_start.sh > /dev/null 2>&1 < /dev/null &
fi

cd -
