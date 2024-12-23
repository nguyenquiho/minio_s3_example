#!/bin/bash
# Absolute path to this script, e.g. /home/user/bin/foo.sh
SCRIPT=$(readlink -f "$0")
# Absolute path this script is in, thus /home/user/bin
SCRIPTPATH=$(dirname "$SCRIPT")

if [ $(ps -ef | grep -v grep | grep msync-s3.php | wc -l) -lt 1 ]; then
	/usr/bin/nohup /usr/bin/php $SCRIPTPATH/msync-s3.php > /dev/null 2>&1 &
else
  echo "There are only 1 process"
  exit 0
fi

