<?php

include_once("semsol-arc2/ARC2.php");
include_once("phpseclib/Math/BigInteger.php");
include_once("phpseclib/Crypt/RSA.php");
include_once("phpseclib/File/X509.php");
include_once("webidauth/WebIdAuth.php");

$webid = new WebIdAuth();


echo "Preparing Database...\n";


$db = new SQLite3('data/webid.db');
$db->exec('CREATE TABLE IF NOT EXISTS comments(webid TEXT, message TEXT, postdate REAL)');

echo "Done!\n";

if($webid->authenticateClient()) {

	echo "AUTHENTICATION SUCCESSFUL\n";
	echo "Your Certificate and WebId are valid.\n";
	echo "WebId: ".$webid->getUri()."\n";
	echo "Name: ".$webid->getName()."\n";
	echo "Public Key: ".$webid->getPublicKey()."\n";

	$webid_uri = $webid->getUri();
	$message = $_REQUEST['message'];

	if($message != null) {
		echo "Message: ".$message."\n";

		if($db->exec("INSERT INTO comments( webid, message, postdate ) VALUES ( '$webid_uri' , '$message', julianday('now') )")) {
			echo "Message successfully saved!\n";
		} else {
			echo "ERROR: Message has not been saved!";
		}
	} else {
		echo "No message set! Try again!";
	}
} else {
	echo "Could not validate the signature of your Certificate with your WebId Public Key.\n";
}

?>
