<?php


include_once("../lib/server-webidauth/WebIdAuth.php");
include_once("../lib/server-webidauth/WebIdData.php");

echo "Preparing Database...\n";

$db = new SQLite3('../data/webid.db');
$db->exec('CREATE TABLE IF NOT EXISTS comments(webid TEXT, message TEXT, postdate REAL)');

echo "Done!\n";

try
{
	$webIdAuth = WebIdAuth::authenticate($_SERVER["SSL_CLIENT_CERT"]);

	if($webIdAuth["status"] === WebIdAuthStatus::AUTH_SUCCESS) {

		$webIdUri = $webIdAuth["x509"]["webIdUri"];

		$webid = new WebIdDocument($webIdUri);

		echo "AUTHENTICATION SUCCESSFUL\n";
		echo "Your Certificate and WebId are valid.\n";
		echo "WebId: ".$webid->getUri()."\n";
		echo "Name: ".$webid->getFoafName()."\n";

		$message = $_REQUEST['message'];

		if($message !== null) {
			echo "Message: ".$message."\n";

			$quotes = array("'");
			$doubles = array("''");
			$message = str_replace($quotes, $doubles, $message);

			if($message !== null && preg_match('/.*\S.*/', $message)) {

				if($db->exec("INSERT INTO comments( webid, message, postdate ) VALUES ( '$webIdUri' , '$message', julianday('now') )")) {
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
		echo WebIdAuthStatus::msg[$webIdAuth["status"]];

		if($webIdAuth["status"] === WebIdAuthStatus::WEBID_NOT_LOADABLE) {
			echo "Passed WebId: ".$webIdAuth["x509"]["webIdUri"];
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


?>
