<?php
/* Functions for interacting with dnstrace main servers.
 *
 * In general, dnst functions return an array containing:
 *    idx 0: boolean indicating success or failure
 *    idx 1: an array or string of the desired response, depending on the function invoked
 */

function dnstLoadVersion() {
	$ch = curl_init("https://dnstrace.pro/api/badger/version");
	
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
	curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);
	curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 2); 
	curl_setopt($ch, CURLOPT_TIMEOUT, 4);
	
	$response = curl_exec($ch);
	$err = curl_error($ch);
	
	if($err) {
		trigger_error("LoadVer: Detected CURL error", E_USER_WARNING);
		return [false, $err];
	} else {
		return [true, $response];
	}
}

function dnstWorkReq($key, $num) {
	$ch = curl_init("https://dnstrace.pro/api/badger_request.php?key=" . $key . "&num=" . $num);
	
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
	curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);
	curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 2); 
	curl_setopt($ch, CURLOPT_TIMEOUT, 4);
	
	$response = curl_exec($ch);
	$err = curl_error($ch);
	
	if($err) {
		trigger_error("WorkReq: Detected CURL error", E_USER_WARNING);
		return [false, $err];
	}
	
	$json = json_decode($response, true);
		
	if(!$json) {
		trigger_error("WorkReq: Detected JSON response invalid", E_USER_WARNING);
		return [false, $response];
	} elseif(!$json[0]) {
		trigger_error("WorkReq: System error. Info:" . $json["Reason"], E_USER_WARNING);
		return [false, $response];
	} else {
		return [true, $json];
	}
}

function dnstWorkGet($key) {
	$ch = curl_init("https://dnstrace.pro/api/badger_get.php?key=" . $key);
	
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
	curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);
	curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 2); 
	curl_setopt($ch, CURLOPT_TIMEOUT, 4);
	
	$response = curl_exec($ch);
	$err = curl_error($ch);
	
	if($err) {
		trigger_error("WorkGet: Detected CURL error", E_USER_WARNING);
		return [false, $err];
	}
	
	$json = json_decode($response, true);
		
	if(!$json) {
		trigger_error("WorkGet: Detected JSON response invalid", E_USER_WARNING);
		return [false, $response];
	} elseif(!$json["Success"]) {
		trigger_error("WorkGet: System error. Info:" . $json["Reason"], E_USER_WARNING);
		return [false, $response];
	} else {
		return [true, $json];
	}
}

function dnstWorkSubmit($key, $fqdn, $type, $res) {
	$ch = curl_init("https://dnstrace.pro/api/badger_submit.php");
	
	$data = http_build_query([
		"key" => $key,
		"fqdn" => $fqdn,
		"type" => $type,
		"result" => $res
	]);
	
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
	curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);
	curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 2); 
	curl_setopt($ch, CURLOPT_TIMEOUT, 4);
	curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'POST');
	curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
	
	$response = curl_exec($ch);
	$err = curl_error($ch);
	
	if($err) {
		trigger_error("WorkSubmit: Detected CURL error", E_USER_WARNING);
		return [false, $err];
	}
	
	$json = json_decode($response, true);
		
	if(!$json) {
		trigger_error("WorkSubmit: Detected JSON response invalid", E_USER_WARNING);
		return [false, $response];
	} elseif(!$json["Success"]) {
		trigger_error("WorkSubmit: System error. Info:" . $json["Reason"], E_USER_WARNING);
		return [false, $response];
	} else {
		return [true, $json];
	}
}