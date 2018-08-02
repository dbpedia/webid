# PHP Code
The PHP Code folder contains the following elements:

|- lib - Libraries
 |- server-webidauth: server code for WebId authentication  
  |- semsol-arc2: rdflib included from https://github.com/semsol/arc2/
|-apps
 |- thanks 
 |- messageboard
 |- viewer


## Requirements
### PHP Versions used during development
```
Ubuntu 16.04. PHP 7.0.30-0ubuntu0.16.04.1 (cli) ( NTS )
Ubuntu 18.04. PHP 7.2.7-0ubuntu0.18.04.2
```
### php-phpseclib used during development
*NOTE* do not confuse with `php-seclib`, it is an older packages

In order to use the server WebId authentication, which is the basis for all other code, you first need to install the `php-phpseclib`.

```
# Ubuntu
sudo apt-get install php-phpseclib
# Tested with
16.04. php-phpseclib (2.0.1-1build1)
18.04. php-phpseclib (2.0.9-1)

```


##  Server WebId Authentication
Folder: lib/server-webidauth



To use WebId Authentication in your application, put a copy of this folder into your project and include the `WebIdAuth.php` file.

```php
<?php
include_once("webid/WebIdAuth.php");

// Your code

?>
```

### Examples

You can find example applications [here](https://github.com/dbpedia/webid/tree/master/code/php/apps). 

### Usage

The `WebIdAuth.php` file contains a number of useful functions for authentication with WebId. To authenticate a client your server code needs to run the following steps

1. Find the PEM encoded client certificate
2. Load the Public Key from the client certificate (The corresponding Private Key has been used to sign the client certificate)
3. Read the WebId URI from the client certificate
4. Load the WebId document at the WebId URI
5. Read the Public Keys from the client's WebId document
6. Match the Public Key from the client certificate against the ones from the WebId document.

Once a key from the WebId document matches the one from the certificate, it is safe to say, that the client has access to the WebId document. Since the WebId document should only be accessible by its owner, the authentication is complete.
Below you can find an example of the usage of the `WebIdAuth` functions (without any validation steps).

```php
<?php
include_once("webid/WebIdAuth.php");

// 1. Find the PEM encoded client certificate
$client_certificate = $_SERVER["SSL_CLIENT_CERT"];

$x509_cert = WebIdAuth::loadX509($client_cert_pem);

// 2. Load the Public Key from the client certificate
$pkey_certificate = $x509_cert["certificatePublicKey"];

// 3. Read the WebId URI from the client certificate
$webid_uri = $x509_cert["webIdUri"];

// 4. Load the WebId document at the WebId URI
$webid_document = WebIdAuth::loadWebId($webid_uri);

// 5. Read the Public Keys from the client's WebId document
$pkeys_webid = $webid_document["webIdPublicKeys"];

// 6. Match the Public Key from the client certificate against the ones from the WebId document
$authenticated = WebIdAuth::hasKeyMatch($pkeys_webid, $pkey_certificate);

?>
```

You can aswell run the `authenticate` function in order to run all of the above commands with a single call. The certificate, WebId documents and authentication result are returned as an array. If anything goes wrong, the result array holds an error message.

```php
<?php
include_once("webid/WebIdAuth.php");


try {
  $webIdAuth = WebIdAuth::authenticate($_SERVER["SSL_CLIENT_CERT"]);
  
  if($webIdAuth["status"] === WebIdAuth::AUTHENTICATION_SUCCESSFUL) {
  
    // Authenticated code
    
  } else {
    echo $webIdAuth["message"];
  }

} catch(Exception $e) {
  echo $e;
}



```


