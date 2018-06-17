<?php

include_once("../semsol-arc2/ARC2.php");
include_once("../phpseclib/Math/BigInteger.php");
include_once("../phpseclib/Crypt/RSA.php");
include_once("../phpseclib/File/X509.php");

$x509 = new File_X509();
$db = new SQLite3('../data/webid.db');

$db->exec('CREATE TABLE IF NOT EXISTS users(webid TEXT PRIMARY KEY, status TEXT)');

// Load the x509 cert from the server variable
$cert = $x509->loadX509($_SERVER['SSL_CLIENT_CERT']);

// Get the WebId URI from the certificate
$webid = $cert["tbsCertificate"]["extensions"][2]["extnValue"][0]["uniformResourceIdentifier"];

// Parse the WebId with a TTL parser
$parser = ARC2::getRDFParser();
$parser->parse($webid);

// Create an index from the parsed TTL
$index = $parser->getSimpleIndex();

// Get modulus and exponent from the index
$rsakey = $index[$webid]["http://www.w3.org/ns/auth/cert#key"][0];
$modulusBinaryString = $index[$rsakey]["http://www.w3.org/ns/auth/cert#modulus"][0];
$exponentBinaryString = $index[$rsakey]["http://www.w3.org/ns/auth/cert#exponent"][0];

// Convert modulus and exponent to BigInteger
$modulus = new Math_BigInteger($modulusBinaryString, 256);
$exponent = new Math_BigInteger($exponentBinaryString, 256);

// Create a new public key
$rsa = new Crypt_RSA();
$rsa->modulus = $modulus;
$rsa->exponent = $exponent;
$rsa->publicExponent = $exponent;
$rsa->k = strlen($rsa->modulus->toBytes());

// Set the public key of the certificate, overriding the existing one
$x509->setPublicKey($rsa);

// Validate the certificate signature with the new public key
if($x509->validateSignature(false)) {

	$status = $_REQUEST['message'];
	if($db->exec("INSERT INTO users ( webid, status ) VALUES ( '$webid', '$status' )")) {
		echo "SUCCESS\n";
		echo "Your Certificate and WebId are valid.\n";
		echo "Your Status has been changed to ".$status."\n\n";
	}
} else {
	echo "Could not validate the signature of your Certificate with your WebId Public Key.\n";
}

$results = $db->query('SELECT * FROM users');

echo "=== REGISTERED WEB IDS ===\n";

while ($row = $results->fetchArray()) { 
    echo "ID: ".$row['webid']."\n";
    echo "STATUS: ".$row['status']."\n";
    echo "=========================\n";
}
?>
