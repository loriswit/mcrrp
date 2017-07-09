#!/usr/bin/env bash

perl -0777 -ne 'print "$2" if /'"$1"':\h*\n(  .*\n|\h*#.*\n|\h*\n)*  '"$2"':([^\n#]*)(#.*)?\n/m' \
    $(dirname "$0")/../config.yml | sed -e 's/^\s*//' -e 's/\s*$//'
