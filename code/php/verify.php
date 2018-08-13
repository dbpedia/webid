<?php

include_once("lib/server-webidauth/WebIdAuth.php");
include_once("lib/server-webidauth/WebIdDocument.php");


echo "Validating your WebId...";

try
{
	$webIdAuth = WebIdAuth::authenticate($_SERVER["SSL_CLIENT_CERT"]);


	if($webIdAuth["status"] === WebIdAuthStatus::AUTH_SUCCESS) {

		$webIdUri = $webIdAuth["x509"]["webIdUri"];

		$webid = new WebIdDocument($webIdUri);

		echo "SUCCESS\n";
		echo "Your Certificate and WebId are valid.\n";
		echo "WebId: ".$webid->getUri()."\n";
		echo "Name: ".$webid->getFoafName()."\n";
		echo "WebId Document:\n";
		print_r($webid->data);


	} else {

		echo WebIdAuthStatus::msg[$webIdAuth["status"]];

		if($webIdAuth["status"] === WebIdAuthStatus::WEBID_NOT_LOADABLE) {
			echo "Passed WebId: ".$webIdAuth["x509"]["webIdUri"]."\n";
		}

		if($webIdAuth["status"] === WebIdAuthStatus::AUTH_FAILED) {
			echo "X509 RSA Key: \n";
			echo $result["x509"]["certificatePublicKey"]."\n";
			echo "WebId RSA Keys:\n";

	    foreach($result["webId"]["webIdPublicKeys"] as $webIdKey) {
				echo $webIdKey."\n";
			}
		}

	}


} catch(Exception $e) {
	echo $e;
}
