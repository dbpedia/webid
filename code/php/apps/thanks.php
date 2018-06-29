<?php


include_once("WebIdAuth.php");
include_once("WebIdData.php");

echo "Preparing Database...\n";


$db = new SQLite3('data/webid.db');
$db->exec('CREATE TABLE IF NOT EXISTS comments(webid TEXT, message TEXT, postdate REAL)');

echo "Done!\n";

try
{
	$webidauth = WebIdAuth::create($_SERVER["SSL_CLIENT_CERT"]);

	if($webidauth->comparePublicKeys()) {

		$webid = new WebIdData($webidauth->webid_uri, $webidauth->webid_data);

		echo "AUTHENTICATION SUCCESSFUL\n";
		echo "Your Certificate and WebId are valid.\n";
		echo "WebId: ".$webid->getUri()."\n";
		echo "Name: ".$webid->getFoafName()."\n";
		echo "Modulus: ".$webid->getCertModulus()."\n";
		echo "Exponent: ".$webid->getCertExponent()."\n";


		$message = $_REQUEST['message'];

		if($message !== null) {
			echo "Message: ".$message."\n";

			$quotes = array("'");
			$doubles = array("''");
			$message = str_replace($quotes, $doubles, $message);

			if($message !== null && preg_match('/.*\S.*/', $message)) {

				$webid_uri = $webid->getUri();

				if($db->exec("INSERT INTO comments( webid, message, postdate ) VALUES ( '$webid_uri' , '$message', julianday('now') )")) {
					echo "Message successfully saved!\n";
				} else {
					echo "ERROR: Message has not been saved!";
				}
			} else {
				echo "Message has an invalid format, try again!";
			}
		} else {
			echo "No message set, try again!";
		}
	} else {
		echo "Could not validate the signature of your Certificate with your WebId Public Key.\n";
	}
} catch(Exception $e) {
	echo $e;
}


?>
