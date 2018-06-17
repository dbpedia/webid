<?php

include_once("semsol-arc2/ARC2.php");
include_once("phpseclib/Math/BigInteger.php");
include_once("phpseclib/Crypt/RSA.php");
include_once("phpseclib/File/X509.php");
include_once("webidauth/WebIdAuth.php");

$webid = new WebIdAuth();

if($webid->authenticateClient()) {

	echo "SUCCESS\n";
	echo "Your Certificate and WebId are valid.\n";
	echo "WebId: ".$webid->getUri()."\n";
	echo "Name: ".$webid->getName()."\n";
	echo "Public Key: ".$webid->getPublicKey()."\n";
} else {
	echo "Could not validate the signature of your Certificate with your WebId Public Key.\n";
}
