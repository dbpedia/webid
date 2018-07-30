<?php

include_once("semsol-arc2/ARC2.php");

/**
* Helper class for easier webid data access.
*
* @package WebId
* @author  Jan Forberg <jan.forberg@hotmail.de>
* @access  public
*/
class WebIdDocument
{

  var $data;

  var $uri;

  function __construct($webIdUri) {
    // Parse the WebId with a TTL parser
    $parser = ARC2::getRDFParser();
    $parser->parse($webIdUri);

    // Create an index from the parsed TTL
    $this->data = $parser->getSimpleIndex();
    $this->uri = $webIdUri;
  }

  function getUri() {
    return $this->uri;
  }

  function getFoafName() {
    return $this->data[$this->uri]["http://xmlns.com/foaf/0.1/name"][0];
  }

  function getFoafImg($fallback) {
    if(!isset($this->data[$this->uri]["http://xmlns.com/foaf/0.1/img"])) {
      return $fallback;
    }

    return $this->data[$this->uri]["http://xmlns.com/foaf/0.1/img"][0];
  }

  function getCertExponent() {
    return $this->data[$this->uri]["http://www.w3.org/ns/auth/cert#key"]["http://www.w3.org/ns/auth/cert#exponent"][0];
  }

  function getCertModulus() {
    return $this->data[$this->uri]["http://www.w3.org/ns/auth/cert#key"]["http://www.w3.org/ns/auth/cert#modulus"][0];
  }
}

?>
