<?php
/* FIFO dequeuer, delegates workers */

require_once "deps/queues/ConcurrentFIFO.php";
require_once "inc/fileIO.php";

$q = new ConcurrentFIFO('fqdns.fifo');

while(true) {
	$doReload = intval(basicRead(getcwd() . "/status/reload"));

	if($doReload != 0) {
		basicWrite(getcwd() . "/status/dequeue", "1");
		exit;
	}
}