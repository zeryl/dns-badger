<?php
/* FIFO dequeuer, delegates workers */

require_once "deps/queue/ConcurrentFIFO.php";
require_once "inc/fileIO.php";

$q = new ConcurrentFIFO('fqdns.fifo');

