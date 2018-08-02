<?php

include_once("lib/webidauth/WebIdAuth.php");
include_once("lib/webidauth/WebIdDocument.php");

try
{
	$webidauth = WebIdAuth::authenticate($_SERVER["SSL_CLIENT_CERT"]);

	if($webIdAuth["status"] === WebIdAuth::AUTHENTICATION_SUCCESSFUL) {

		$webIdUri = $webIdAuth["x509"]["webIdUri"]

		$webid = new WebIdDocument($webIdUri);

		echo "SUCCESS\n";
		echo "Your Certificate and WebId are valid.\n";
		echo "WebId: ".$webid->getUri()."\n";
		echo "Name: ".$webid->getFoafName()."\n";
		echo "Public Key: ".$webid->getPublicKey()."\n";

	} else {
		echo $webIdAuth["message"];
	}


} catch(Exception $e) {
	echo $e;
}
