#!/bin/bash

# script set in background
setsid /usr/local/sbin/prep_oc.sh > output.txt &

# run systemd
exec /usr/sbin/init
