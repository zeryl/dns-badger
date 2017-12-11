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
		echo "0" > status/reload
		echo "0" > status/enqueue
		echo "0" > status/dequeue
		nohup php enqueue.php > /tmp/dnsb-enq.log 2>&1 &
		sleep 30
		nohup php dequeue.php > /tmp/dnsb-deq.log 2>&1 &
	else
		sleep 10
		tryrem=$(( tryrem - 1 ))
		echo "git pull failed"
	fi
done
