<?php
/* FIFO enqueuer, keeps caches hot */

require_once "deps/queues/ConcurrentFIFO.php";
require_once "deps/vendor/autoload.php";
require_once "inc/fileIO.php";

$q = new ConcurrentFIFO('fqdns.fifo');

while(true) {
	if($q->count() < 
	sleep(1);
	
	$doReload = intval(basicRead(getcwd() . "/status/reload"));

	if($doReload != 0) {
		basicWrite(getcwd() . "/status/enqueue", "1");
		exit;
	}
}