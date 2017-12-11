<?php
/* FIFO dequeuer, worker thread */

require_once "deps/queues/ConcurrentFIFO.php";
require_once "deps/vendor/autoload.php";
require_once "inc/fileIO.php";

$q = new ConcurrentFIFO('fqdns.fifo');

while(true) {
	//sleep(1);
}