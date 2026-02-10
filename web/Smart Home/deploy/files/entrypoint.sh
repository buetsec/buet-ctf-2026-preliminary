#!/usr/bin/env sh
set -eu

: "${FLAG:=buetctf{test_flag}}"

chmod 0600 /flag.txt 2>/dev/null || true
printf "%s" "$FLAG" > /flag.txt
chmod 0400 /flag.txt || true

mkdir -p ./data
: > ./data/activity.log


exec "$@"
