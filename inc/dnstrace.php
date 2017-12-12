<?php
/* Functions for interacting with dnstrace main servers.
 *
 * In general, dnst functions return an array containing:
 *    idx 0: boolean indicating success or failure
 *    idx 1: an array or string of the desired response, depending on the function invoked
 */

function dnstLoadVersion() {
	$ch = curl_init("https://dnstrace.pro/api/badger/version/");
	
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
	curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);
	curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 2); 
	curl_setopt($ch, CURLOPT_TIMEOUT, 4);
	
	$response = curl_exec($ch);
	$err = curl_error($ch);
	
	if($err) {
		trigger_error("Detected CURL error when querying dnstrace", E_USER_WARNING);
		return [false, $err];
	} else {
		return [true, $response];
	}
}

function dnstWorkReq() {
	$ch = curl_init("https://dnstrace.pro/api/badger_get.php");
	
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
	curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);
	curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 2); 
	curl_setopt($ch, CURLOPT_TIMEOUT, 4);
	
	$response = curl_exec($ch);
	$err = curl_error($ch);
	
	if($err) {
		trigger_error("Detected CURL error when querying dnstrace", E_USER_WARNING);
		return [false, $err];
	} else {
		return [true, $response];
	}
}

function dnstWorkGet() {
	$ch = curl_init("https://dnstrace.pro/api/badger/");
	
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
	curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);
	curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 2); 
	curl_setopt($ch, CURLOPT_TIMEOUT, 4);
	
	$response = curl_exec($ch);
	$err = curl_error($ch);
	
	if($err) {
		trigger_error("Detected CURL error when querying dnstrace", E_USER_WARNING);
		return [false, $err];
	} else {
		return [true, $response];
	}
}

function dnstWorkSubmit($key, $res) {
	$ch = curl_init("https://dnstrace.pro/api/badger_submit.php");
	
	$data = http_build_query([
		"key" => $key,
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
		trigger_error("Detected CURL error when querying dnstrace", E_USER_WARNING);
		return [false, $err];
	} else {
		return [true, $response];
	}
}