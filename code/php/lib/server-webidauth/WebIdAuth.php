<?php

include_once("semsol-arc2/ARC2.php");
include_once("phpseclib/File/X509.php");
include_once("phpseclib/File/ASN1.php");
include_once("phpseclib/Math/BigInteger.php");
include_once("phpseclib/Crypt/RSA.php");
include_once("phpseclib/Crypt/Hash.php");
include_once("WebIdAuthStatus.php");

use phpseclib\File\X509;
use phpseclib\Math\BigInteger;
use phpseclib\Crypt\RSA;

/**
* Manages authentication using WebId.
*
* @package WebId
* @author  Jan Forberg <jan.forberg@hotmail.de>
* @access  public
*/
class WebIdAuth
{

  const AUTHENTICATION_SUCCESSFUL = 0;

  const AUTHENTICATION_FAILED = 1;

  const INVALID_CERTIFICATE = 2;

  /**
  * Loads x509 cert, WebId document and does the authentication
  * @param  [type] $client_cert_pem [description]
  * @return [type]                  [description]
  */
  static function authenticate($client_cert_pem) {

    $result = array();

    // ERROR: No certificate passed
    if($client_cert_pem === null || $client_cert_pem === "") {
      $result["status"] = WebIdAuthStatus::CERT_NOT_PASSED;
      return $result;
    }

    // Load the x509 certificate
    $result["x509"] = WebIdAuth::loadX509($client_cert_pem);

    // Validate the x509 certificate
    if(!isset($result["x509"]["webIdUri"])) {
      $result["status"] = WebIdAuthStatus::CERT_SAN_NOT_SET;
      return $result;
    }

    $webIdUri = $result["x509"]["webIdUri"];

    //TODO: Validate webId uri, set WebIdAuthStatus::CERT_MALFORMED_URL


    // Load the WebId document
    $result["webId"] = WebIdAuth::loadWebId($webIdUri);

    if(isset($result["webId"]["errors"])) {
      $result["status"] = WebIdAuthStatus::WEBID_NOT_LOADABLE;
      return $result;
    }

    // Validate the WebId document
    if(!isset($result["webId"]["webIdPublicKeys"])) {
      $result["status"] = WebIdAuthStatus::WEBID_NO_RSA_KEYS;
      return $result;
    }

    // TODO: Validiate RSA key , set WebIdAuthStatus::WEBID_MALFORMED_RSA_KEY

    // Validate the certificate signature
    if(!WebIdAuth::hasKeyMatch($result["webId"]["webIdPublicKeys"], $result["x509"]["certificatePublicKey"])) {
      $result["status"] = WebIdAuthStatus::AUTH_FAILED;
      return $result;
    }

    // Authentication successful!
    $result["status"] = WebIdAuthStatus::AUTH_SUCCESS;
    return $result;
  }

  /**
  * Loads a x509 certificate from a PEM encoded string and checks for a webid in the SAN field.
  * Throws MalformedX509Exception incase an invalid certificate gets passed.
  * @param  string $client_cert_pem PEM encoded x509 certificate
  * @return array                   returns an array holding the x509 data
  */
  static function loadX509($client_cert_pem) {

    $x509 = new X509();
    $certificate = $x509->loadX509($client_cert_pem);

    $certificate["certificatePublicKey"] = $certificate["tbsCertificate"]["subjectPublicKeyInfo"]["subjectPublicKey"];

    // Check if the SAN field contains the WebId URI
    if(isset($certificate["tbsCertificate"]["extensions"][2]["extnValue"][0]["uniformResourceIdentifier"])) {
      $certificate["webIdUri"] = $certificate["tbsCertificate"]["extensions"][2]["extnValue"][0]["uniformResourceIdentifier"];
    }

    return $certificate;
  }

  /**
  * Loads a WebId to a data array, also tranlates public keys to PEM format, if present
  * @param  string $webIdUri The WebId uri
  * @return array            The array containing the WebId data
  */
  static function loadWebId($webIdUri) {

    // TODO: check webid uri
    // Parse the WebId with a TTL parser
    $parser = ARC2::getRDFParser();
    $parser->parse($webIdUri);

    $webIdData = array();

    if($parser->getErrors() !== null && sizeof($parser->getErrors()) > 0) {
      $webIdData["errors"] = $parser->getErrors();
      return $webIdData;
    }

    // Create an index from the parsed TTL
    $webIdData = $parser->getSimpleIndex();



    if(isset($webIdData[$webIdUri]["http://www.w3.org/ns/auth/cert#key"])) {

      $webIdPublicKeys = array();
      $keys = $webIdData[$webIdUri]["http://www.w3.org/ns/auth/cert#key"];

      // Loop over all keys, translate to PEM format
      foreach($keys as $key) {

        $modulus = strtoupper($webIdData[$key]["http://www.w3.org/ns/auth/cert#modulus"][0]);
        $exponent = $webIdData[$key]["http://www.w3.org/ns/auth/cert#exponent"][0];

        $webIdData["webIdPublicKeys"][] = WebIdAuth::modexp2PEM($modulus, $exponent);
      }
    }

    return $webIdData;
  }

  /**
  * Compares the public keys of WebId and x509 certificate.
  * @return bool true, if public keys  match, false otherwise
  */
  static function hasKeyMatch($webIdKeys, $certKey) {

    if($certKey === null) {
      return FALSE;
    }

    foreach($webIdKeys as $webIdKey) {
      if($webIdKey === $certKey) {
        return TRUE;
      }
    }

    return FALSE;
  }

  /**
  * Converts a modulus and exponent to the PEM encoded public key formatted
  * @param  string $modulus  modulus as a String
  * @param  string $exponent exponent as a string
  * @return string           the PEM encoded public key as a String
  */
  static function modexp2PEM($modulus, $exponent) {

    // Convert modulus and exponent to BigInteger
    $modulusBigInt = new BigInteger($modulus, 16);
    $exponentBigInt = new BigInteger($exponent);

    // Create public key from modulus and exponent
    $rsa = new RSA();
    $rsa->modulus = $modulusBigInt;
    $rsa->exponent = $exponentBigInt;
    $rsa->publicExponent = $exponentBigInt;
    $rsa->k = strlen($rsa->modulus->toBytes());

    return $rsa->getPublicKey(RSA::PUBLIC_FORMAT_PKCS1);
  }
}

try
{

} catch(Exception $e) {
  echo $e;
}

?>
