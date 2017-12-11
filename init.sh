#!/bin/bash
currscr=`realpath $0`
scrpath=`dirname $currscr`
cd $scrpath

# inspired by archiveteam warrior boot handling
tryrem=6
while [[ $tryrem -gt 0 ]]
do
  if git pull
  then
    tryrem=0
	echo "pull succeeded, starting program"
	rm fqdns.fifo
	echo "test" > gucci.fifo
  else
    sleep 10
    tryrem=$(( tryrem - 1 ))
    echo "git pull failed"
  fi
done
