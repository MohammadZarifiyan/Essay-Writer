#!/bin/sh
set -eu

output="$(supervisorctl status)" || exit 1
[ -n "$output" ] || exit 1

printf '%s\n' "$output" |
  awk 'NF < 2 || $2 != "RUNNING" { failed = 1 }
       END { if (NR == 0 || failed) exit 1 }'

curl -f http://localhost/up || exit 1
