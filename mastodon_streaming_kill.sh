#!/bin/bash -x

PIDS=(`ps aux | grep mastodon_streaming | grep php | grep -v grep | awk '{ print $2; }'`)
for pid in ${PIDS[*]}
do
    kill -9 ${pid}
done
