<?php
/* FIFO dequeuer, worker thread */

require_once "deps/queues/ConcurrentFIFO.php";
require_once "deps/vendor/autoload.php";
require_once "inc/fileIO.php";
require_once "inc/gdns.php";
require_once "inc/dnstrace.php";

$q = new ConcurrentFIFO('fqdns.fifo');

while(true) {
	sleep(1);
	
	$doReload = intval(basicRead(getcwd() . "/status/reload"));
	
	if($doReload != 0 && $q->count() == 0) {
		exit;
	}
}