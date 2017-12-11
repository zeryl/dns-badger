<?php
/* FIFO enqueuer, keeps caches hot */

require_once "deps/queue/ConcurrentFIFO.php";
require_once "inc/fileIO.php";

$q = new ConcurrentFIFO('fqdns.fifo');

