<?php
/* Version/reload checker, informs other processes to halt */

chdir(dirname(__FILE__));

require_once "inc/fileIO.php";
require_once "inc/dnstrace.php";

$currVersion = intval(basicRead(getcwd() . "/version"));
$doReload = intval(basicRead(getcwd() . "/status/reload"));

if($doReload != 0) {
	exit;
}

$getRemoteVer = dnstLoadVersion();

if($getRemoteVer[0]) {
	$reVersion = intval($getRemoteVer[1]);
	if($reVersion > $currVersion) {
		basicWrite(getcwd() . "/status/reload", "1");
		echo "Initiating upgrade, stopping worker gracefully\n";
		
		while(true) {
			$rdyEnqueue = intval(basicRead(getcwd() . "/status/enqueue"));
			
			if($rdyEnqueue == 1) {
				exec("nohup bash init.sh >> /tmp/dnsb-init.log 2>&1 &");
				echo "System restarting\n";
				exit;
			} else {
				sleep(15);
			}
		}
	}
} else {
	echo "could not contact dnstrace.pro";
}
?>