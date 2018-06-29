<?php

//set_include_path(get_include_path() . DIRECTORY_SEPARATOR . 'phpseclib');
include_once("phpseclib/Math/BigInteger.php");
include_once("phpseclib/Crypt/RSA.php");
include_once("phpseclib/Crypt/Hash.php");
include_once("phpseclib/File/X509.php");
include_once("phpseclib/File/ASN1.php");

include_once("semsol-arc2/ARC2.php");

/**
* Manages authentication using WebId.
*
* @package WebIdAuthentication
* @author  Jan Forberg <jan.forberg@hotmail.de>
* @access  public
*/
class WebIdAuth
{
  /**
  * Web Id URI
  *
  * @var string
  * @access private
  */
  var $webid_uri;

  /**
  * Web Id Dara array
  *
  * @var string
  * @access private
  */
  var $webid_data;

  /**
  * Web Id Public Key
  *
  * @var string
  * @access private
  */
  var $webid_public_key;

  /**
  * x509 Client Certificate
  *
  * @var File_X509
  * @access private
  */
  var $x509;

  var $x509_data;

  var $x509_public_key;

  function __construct() {

  }

  /** Authenticates a client by a passed client Certificate */
  static function create($client_cert) {

    $auth = new WebIdAuth();

    $auth->loadX509Cert($client_cert);

    $auth->loadWebId($auth->webid_uri);

    return $auth;
  }

  /**
   * Loads a x509 certificate from a given client certificate pem string
   * @param  string $client_cert_pem  The client certificate as PEM encoded String
   * @return array                    the x509 certificate as data array
   */
  function loadX509Cert($client_cert_pem) {

    // Create a new X509 object
    $this->x509 = new phpseclib\File\X509();

    // Load the PEM encoded certificate, returns data array or false
    $this->x509_data = $this->x509->loadX509($client_cert_pem);

    if($this->x509_data === false) {
      throw new InvalidArgumentException("The passed PEM certificate could not be parsed: ".print_r($this->x509_data));
    }

    // Get the WebId URI from the certificate data array
    try {
      $this->webid_uri = $this->x509_data["tbsCertificate"]["extensions"][2]["extnValue"][0]["uniformResourceIdentifier"];
      $this->x509_public_key = $this->x509_data["tbsCertificate"]["subjectPublicKeyInfo"]["subjectPublicKey"];

    } catch(Exception $e) {
      throw new InvalidArgumentException("The passed Certificate is formatted incorrectly. Please check your x509 certificate");
    }

    if($this->webid_uri === null || $this->webid_uri === '') {
      throw new InvalidArgumentException("The passed Certificate is not set up for WebId Authentication. Make sure to write your Webid to the Subject Alternate Name field");
    }

    if($this->x509_public_key === null || $this->x509_public_key === '') {
      throw new InvalidArgumentException("The passed Certificate is not set up for WebId Authentication. The certificate did not contain the required public key information");
    }

    // Return the data array
    return $this->x509_data;
  }

  /**
   * Loads a webId from an URI
   * @param  string $webid_uri  The webid URI
   * @return array              The webId as a data array
   */
  function loadWebId($webid_uri) {

    // Parse the WebId with a TTL parser
    $parser = ARC2::getRDFParser();
    $parser->parse($webid_uri);

    // Create an index from the parsed TTL
    $this->webid_data = $parser->getSimpleIndex();

    try {
      // Get modulus and exponent from the webid index
      $rsakey = $this->webid_data[$this->webid_uri]["http://www.w3.org/ns/auth/cert#key"][0];
      $modulus = strtoupper($this->webid_data[$rsakey]["http://www.w3.org/ns/auth/cert#modulus"][0]);
      $exponent = $this->webid_data[$rsakey]["http://www.w3.org/ns/auth/cert#exponent"][0];


      // Convert modulus and exponent to PEM public key
      $this->webid_public_key = $this->modexp2PEM($modulus, $exponent);

    } catch(Exception $e) {
      throw new InvalidArgumentException("The WebId document at the passed URI is not in the correct format.
      Make sure to publish your Public Key information correctly. (Refer to https://github.org/dbpedia/webid)");
      }

      return $this->webid_data;
    }

    /**
     * Compares the public keys of WebId and x509 certificate.
     * @return bool true, if public keys  match, false otherwise
     */
    function comparePublicKeys() {

      if($this->x509_public_key === null || $this->x509_public_key === '') {
        throw new Exception("Invalid call to validate. Make sure to load a x509 certificate first");
      }

      if($this->webid_public_key === null || $this->webid_public_key === '') {
        throw new Exception("Invalid call to validate. Make sure to load a webId first");
      }

      return $this->webid_public_key == $this->x509_public_key;
    }

    /**
     * Converts a modulus and exponent to the PEM encoded public key formatted
     * @param  string $modulus  modulus as a String
     * @param  string $exponent exponent as a string
     * @return string           the PEM encoded public key as a String
     */
    function modexp2PEM($modulus, $exponent) {

      // Convert modulus and exponent to BigInteger
      $modulusBigInt = new phpseclib\Math\BigInteger($modulus, 16);
      $exponentBigInt = new phpseclib\Math\BigInteger($exponent);

      // Create public key from modulus and exponent
      $rsa = new phpseclib\Crypt\RSA();
      $rsa->modulus = $modulusBigInt;
      $rsa->exponent = $exponentBigInt;
      $rsa->publicExponent = $exponentBigInt;
      $rsa->k = strlen($rsa->modulus->toBytes());

      return $rsa->getPublicKey(phpseclib\Crypt\RSA::PUBLIC_FORMAT_PKCS1_RAW);
    }
}


?>
