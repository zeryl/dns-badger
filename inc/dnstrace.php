<?php
/* Functions for interacting with dnstrace main servers.
 *
 * In general, dnst functions return an array containing:
 *    idx 0: boolean indicating success or failure
 *    idx 1: an array or string of the desired response, depending on the function invoked
 */

function dnstLoadVersion() {
	$ch = curl_init("https://dnstrace.pro/badger/version");
	
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
	curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);
	
	$response = curl_exec($ch);
	$err = curl_error($ch);
	
	if($err) {
		trigger_error("Detected CURL error when querying dnstrace", E_USER_WARNING);
		return [false, $err];
	} else {
		return [true, $response];
	}
}