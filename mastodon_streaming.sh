#!/bin/bash -x

SCRIPT_DIR=$(cd $(dirname $0);pwd)
echo ${SCRIPT_DIR}
cd ${SCRIPT_DIR}

MASTODON_LABOTPROC=`ps aux | grep mastodon_streaming | grep php | grep -v grep | awk '{ print $2; }'`
if [ ! ${MASTODON_LABOTPROC} ]
then
    nohup ./mastodon_streaming_start.sh > /dev/null 2>&1 < /dev/null &
fi

cd -
