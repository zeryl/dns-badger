<?php
/* FIFO enqueuer, keeps caches hot */

require_once "deps/queues/ConcurrentFIFO.php";
require_once "deps/vendor/autoload.php";
require_once "inc/fileIO.php";

$q = new ConcurrentFIFO('fqdns.fifo');
$maxThru = intval(basicRead(getcwd() . "/maxThroughput"));

while(true) {
	if($q->count() < ($maxThru * 30)) {
		
	}
	
	sleep(1);
	
	$doReload = intval(basicRead(getcwd() . "/status/reload"));

	if($doReload != 0) {
		while(true) {
			if($q->count() == 0) {
				sleep(30); // allow dequeues to finish gracefully
				basicWrite(getcwd() . "/status/enqueue", "1");
				exit;
			} else {
				sleep(10);
			}
		}
	}
}