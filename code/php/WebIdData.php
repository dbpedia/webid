<?php


/**
* Helper class for easier webid data access.
*
* @package WebIdAuthentication
* @author  Jan Forberg <jan.forberg@hotmail.de>
* @access  public
*/
class WebIdData
{

  var $data;

  var $uri;

  function __construct($webid_uri, $webid_data) {

    $this->data = $webid_data;
    $this->uri = $webid_uri;
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
