<?php

/**
 * Authentication using WebId
 *
 * @package WebIdAuthentication
 * @author  Jan Forberg <jan.forberg@hotmail.de>
 * @access  public
 */
class WebIdAuth
{
	/**
     * Client Cert (ie. e or d)
     *
     * @var String
     * @access public
     */
    var $client_cert;

    /**
     * Web Id URI 
     *
     * @var String
     * @access private 
     */
    var $webid_uri;

     /**
     * Web Id Name 
     *
     * @var String
     * @access private 
     */
    var $webid_name;

	/**
     * Web Id Public Key 
     *
     * @var String
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

    /**
     * Refreshes the internal data with the current client_cert
     */
	function parseData() {

		// Load the x509 cert from the server variable
		$this->x509 = new File_X509();
		$cert = $this->x509->loadX509($this->client_cert);

		// Get the WebId URI from the certificate
		$this->webid_uri = $cert["tbsCertificate"]["extensions"][2]["extnValue"][0]["uniformResourceIdentifier"];
		$this->webid_name = $cert["tbsCertificate"]["subject"]["rdnSequence"][6][0]["value"]["utf8String"];

		// Parse the WebId with a TTL parser
		$parser = ARC2::getRDFParser();
		$parser->parse($this->webid_uri);

		// Create an index from the parsed TTL
		$index = $parser->getSimpleIndex();

		// Get modulus and exponent from the index
		$rsakey = $index[$this->webid_uri]["http://www.w3.org/ns/auth/cert#key"][0];
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

		$this->webid_public_key = $rsa->getPublicKey();

		// Set the public key of the certificate, overriding the existing one
		$this->x509->setPublicKey($rsa);
	}

	function __construct() {
		$this->client_cert = $_SERVER["SSL_CLIENT_CERT"];

		$this->parseData();
	}

	/*
	 * Authenticates the client, returns true or false
	 */
	function authenticateClient() {

		$this->parseData();

		return $this->x509->validateSignature(false);
	}

	/*
	 * Returns the WebId URI
	 */
	function getUri() {
		return $this->webid_uri;
	}

	/*
	 * Returns the WebId Name
	 */
	function getName() {
		return $this->webid_name;
	}

	/*
	 * Returns the WebId Public Key
	 */
	function getPublicKey() {
		return $this->webid_public_key;
	}
}

?>
