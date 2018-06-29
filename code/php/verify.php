<?php

include_once("WebIdAuth.php");
include_once("WebIdData.php");

try
{
	$webidauth = WebIdAuth::create($_SERVER["SSL_CLIENT_CERT"]);

	if($webidauth->comparePublicKeys()) {

		$webid = new WebIdData($webidauth->webid_uri, $webidauth->webid_data);

		echo "SUCCESS\n";
		echo "Your Certificate and WebId are valid.\n";
		echo "WebId: ".$webidauth->webid_uri."\n";
		echo "Name: ".$webid->getFoafName()."\n";
		echo "Public Key: ".$webid->getPublicKey()."\n";
	} else {
		echo "Could not validate the signature of your Certificate with your WebId Public Key.\n";
	}
} catch(Exception $e) {
	echo $e;
}
