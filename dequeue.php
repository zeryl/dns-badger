<?php
/* FIFO dequeuer, worker thread */

require_once "deps/queues/ConcurrentFIFO.php";
require_once "deps/vendor/autoload.php";
require_once "inc/fileIO.php";
require_once "inc/gdns.php";
require_once "inc/dnstrace.php";

$q = new ConcurrentFIFO('fqdns.fifo');
$ID = basicRead(getcwd() . "/nodeID");

use LayerShifter\TLDExtract\Extract;
$ext = new Extract(null, null, Extract::MODE_ALLOW_ICANN);

while(true) {
	sleep(1);
	
	$FQDN = json_decode($q->dequeue(), true);
	if($FQDN !== null) {
		$res = [];
		$parsedDomain = $ext->parse($FQDN[0]);
		
		$gdnsResA = gdnsLooper($parsedDomain->getFullHost(), "A");
		if($gdnsResA[0]) {
			foreach($gdnsResA[1] as $rawResult) {
				if(filter_var($rawResult, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4)) {
					$res[] = array("Q" => "A", "R" => $rawResult);
				}
			}
		}

		$gdnsResAAAA = gdnsLooper($parsedDomain->getFullHost(), "AAAA");
		if($gdnsResAAAA[0]) {
			foreach($gdnsResAAAA[1] as $rawResult) {
				if(filter_var($rawResult, FILTER_VALIDATE_IP, FILTER_FLAG_IPV6)) {
					$res[] = array("Q" => "AAAA", "R" => $rawResult);
				}
			}
		}

		$gdnsResCNAME = gdnsLooper($parsedDomain->getFullHost(), "CNAME");
		if($gdnsResCNAME[0]) {
			foreach($gdnsResCNAME[1] as $rawResult) {
				$parsedRes = $ext->parse(rtrim($rawResult, "."));
				if($parsedRes->isValidDomain()) {
					$res[] = array("Q" => "CN", "R" => $parsedRes->getFullHost());
				}
			}
		}

		$gdnsResMX = gdnsLooper($parsedDomain->getFullHost(), "MX");
		if($gdnsResMX[0]) {
			foreach($gdnsResMX[1] as $rawResult) {
				$arrRes = explode(" ", $rawResult);
				if(count($arrRes) == 1) {
					$parsedRes = $ext->parse(rtrim($arrRes[0], "."));
				} else {
					$parsedRes = $ext->parse(rtrim($arrRes[1], "."));
				}
				if($parsedRes->isValidDomain()) {
					$res[] = array("Q" => "MX", "R" => $parsedRes->getFullHost());
				}
			}
		}

		$gdnsResNS = gdnsLooper($parsedDomain->getFullHost(), "NS");
		if($gdnsResNS[0]) {
			foreach($gdnsResNS[1] as $rawResult) {
				$ext = new Extract(null, null, Extract::MODE_ALLOW_ICANN);
				$parsedRes = $ext->parse(rtrim($rawResult, "."));
				if($parsedRes->isValidDomain()) {
					$res[] = array("Q" => "A", "R" => $parsedRes->getFullHost());
				}
			}
		}
		
		if(count($res) == 0) {
			$res[] = array("Q" => "ALL", "R" => "NONE");
		}
		
		$completed = false;
		while(!$completed) {
			$workSubmit = dnstWorkSubmit($ID, $parsedRes->getFullHost(), "DNS", json_encode($res));
			if($workSubmit[0]) {
				$completed = true;
			} else {
				sleep(5);
			}
		}
	}
	
	$doReload = intval(basicRead(getcwd() . "/status/reload"));
	
	if($doReload != 0 && $q->count() == 0) {
		exit;
	}
}