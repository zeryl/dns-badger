<?php
/* FIFO enqueuer, keeps caches hot */

require_once "deps/queues/ConcurrentFIFO.php";
require_once "deps/vendor/autoload.php";
require_once "inc/fileIO.php";
require_once "inc/dnstrace.php";

$q = new ConcurrentFIFO('fqdns.fifo');
$ID = basicRead(getcwd() . "/nodeID");
$maxThru = intval(basicRead(getcwd() . "/maxThroughput"));

while(true) {
	if($q->count() < ($maxThru * 30)) {
		
		$completed = false;
		while(!$completed) {
			$workReq = dnstWorkReq($ID, ($maxThru * 90));
			if($workReq[0]) {
				echo "Requested work\n";
				$completed = true;
				sleep(2);
			} else {
				sleep(5);
			}
		}
		
		$completed = false;
		while(!$completed) {
			$workGet = dnstWorkGet($ID);
			if($workGet[0]) {
				echo "Got work\n";
				$completed = true;
			} else {
				sleep(2);
			}
		}
		
		foreach($FQDN as $workGet[1]["Todo"]) {
			$q->enqueue(json_encode($FQDN));
		}
		
		$completed = false;
		while(!$completed) {
			$workConfirm = dnstWorkConfirm($ID);
			if($workConfirm[0]) {
				echo "Confirmed work\n";
				$completed = true;
			} else {
				sleep(2);
			}
		}
	}
	
	sleep(1);
	
	$doReload = intval(basicRead(getcwd() . "/status/reload"));

	if($doReload != 0) {
		while(true) {
			if($q->count() == 0) {
				exec("ps ax", $psOut);
				$psCtr = 0;
				foreach($psOut as $process) {
					if(strpos($process, "php dequeue.php") != false) {
						$psCtr++;
					}
				}
				if($psCtr == 0) {
					basicWrite(getcwd() . "/status/enqueue", "1");
					exit;
				}
			}
			sleep(10);
		}
	}
}