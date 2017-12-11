<?php
/* Version/reload checker, informs other processes to halt */

require_once "inc/fileIO.php";
require_once "inc/dnstrace.php";

$currVersion = intval(basicRead("version"));
$doReload = intval(basicRead("status/reload"));

if($doReload != 0) {
	exit;
}

$getRemoteVer = dnstLoadVersion();

if($getRemoteVer[0]) {
	$reVersion = intval($getRemoteVer[1]);
	if($reVersion > $currVersion) {
		basicWrite("status/reload", "1");
		echo "Initiating upgrade, stopping worker gracefully";
		
		while(true) {
			$rdyEnqueue = intval(basicRead("status/enqueue"));
			$rdyDequeue = intval(basicRead("status/dequeue"));
			
			if($rdyEnqueue == 1 && $rdyDequeue == 1) {
				exec("nohup bash > /tmp/dnsb-init.log 2>&1 &");
				exit;
			} else {
				sleep(15);
			}
		}
	}
}
?>