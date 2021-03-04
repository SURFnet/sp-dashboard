#!/bin/bash

# script set in background
setsid /tmp/prep_oc.sh > output.txt &

# run systemd
exec /usr/sbin/init
