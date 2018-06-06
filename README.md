# WebID
WebID Creation and Validation (Tutorial, Tools, Best practices)

# Why WebID?
WebID is a novel way to identify and authenticate users and organisations on the Web with established methods. The main advantages are:
* people and organisations become the main authority for their data with full control
* No need for passwords any more. Authentication is done with your own secure key

In comparison to any other services like Facebook, LinkedIn or OpenID you are the owner of the data and of the secure key and therefore your own authority.
The WebID workflow uses public/private key cryptography and client certifcate authorization to establish secure, authenticated HTTPS connections. 
We compiled a list of background reading at the end. 
 

# Terminology
WebID or WebID URI - The WebID itself is an identifier that represents a person or an organisation. In simple terms it is a self-chosen URI that can be used to retrieve additional information in form of the WebID profile document via the browser or with software (e.g. `curl`). Note the duplicate in WebID(entifier) and Uniform Resource Identifier (URI).     
Examples:

WebID profile document or WebID file - The WebID profile document is the file that is downloaded when you execute a HTTP(S) request  and contains the identifier and all relevant information about the person and organisation in particular the RSA Public Key used to verify authentification. The file is normally written in Turtle Syntax (RDF).

`#` fragment - In website URLs the `#` is used to jump to certain parts of a HTML page, usually a paragraph. In WebID, the URL of the WebID profile document is appended by a fragment `#this` or `#me` to distinguish between the URL of the WebID profile document (the whole file) and the identifier and data in the document (part of the file).  

Client Certifcate or PKCS12 File (*.pfx;*.p12) - The file that contains an X.509 certificate along with the private key to establish the secure connection and authentification. 

# Example 1
This is Jan's WebID: `http://holycrab13.github.io/webid.ttl#this`
If you open it in a browser or `curl` it the `#this` is ignored and the full turtle file is retrieved. In the turtle file you can find a section with additional information about Jan as well as his public key:
* HTTP retrieval: `curl -H "Accept: text/turtle" "http://holycrab13.github.io/webid.ttl#this"`
* Retrival and RDF parsing : `rapper -i turtle http://holycrab13.github.io/webid.ttl#this`
```
# Note that <#this> is a relative path for the webid resolving to the full URI once the RDF is parsed.
<#this> a foaf:Person ;
   foaf:name "Jan Forberg";
   cert:key [ 
       a cert:RSAPublicKey;
       rdfs:label "made on 23 November 2011 on my laptop";
       cert:modulus "978CD24[...]3F29B"^^xsd:hexBinary;
       cert:exponent "65537"^^xsd:integer;
      ] .
``` 

# Example 2
We prepared a full example in the example folder in this repository: https://github.com/dbpedia/webid/tree/master/example
Note that this also contains the files `private_key.pem` and `certificate.pfx` , which are normally *not* public. 

# The Rules of WebID
1. The WebID profile document is editable only by its owner or the owning organisation.
2. The WebID profile document is retrievable as Turtle file via HTTP(S) request on the WebID URI. Use of `Accept: text/turtle` header is mandatory for the request.  
3. The WebID profile document `must` contain:
 * A `foaf:primaryTopic` from the WebID profile URI to the WebID.
 * A statement whether the WebID is a `foaf:Person`, `foaf:Organisation` or `foaf:Agent`
 * The modulus and exponent of the RSAPublicKey
4. The WebID profile document `should` be public as in open access like the majority of websites. 
5. The private key as well as the PKCS12 (.pfx,.p12) file `must` be kept in a secure location (normally password protected file or entrusted to a web browser).  
6. The X.509 Certificate, which is part of the PKCS12 file `must` contain the WebID in the `subject alternative name` (SAN) field


Note: The WebID profile document can contain any amount of additional information and extensions in the document. Some applications may require additional information. 

# Setup

## Public/Private Key Generation
### Generation of the private and public key
#### Ubuntu (openssl)

```
# This will create two files in PEM format, private_key.pem and public_key.pem
openssl genpkey -algorithm RSA -out private_key.pem -pkeyopt rsa_keygen_bits:2048
openssl rsa -pubout -in private_key.pem -out public_key.pem
```

### Modulus and exponent
For the WebID, you will need the modulus and exponent of your public_key.
#### Ubuntu (openssl)
```
# Note that the public key was generated from the private key in the first place. 
# To print the modulus without separators for copy/paste into your WebId document, run
openssl rsa -noout -modulus -in private_key.pem
# This command will print out modulus (ignore, wrong format) and exponent of your public key. 
openssl rsa -pubin -inform PEM -text -noout < public_key.pem

```

## WebID and WebID profile document
### Choose the URI
Before publishing your WebID, think about the URI and the hosting space. Different options are documented below. 
Basically, you will need some webspace to put a file there. The URL of this file will make up the first part of your WebID (plus `#this`). There are actually many, many, many ways to do it. The main benefit of using just a Turtle/RDF file are, that you just need to publish one file. Turtle allows to use the file URL as `@base` for all the relative URIs described, see https://www.w3.org/TR/turtle/#sec-intro . 
  

#### Github.io
A simple way to get a WebID is using Github.io Pages as a free hosting service. 
GitHub page setup is explained here: https://pages.github.com/
(Basically it is creating a repository with the name `${username}.github.io` and creating a `webid.ttl` there, your WebId will then be <YOUR_GITHUB_NAME>.github.io/webid.ttl#this (see example above).
Here is the repo of Jan: https://github.com/holycrab13/holycrab13.github.io leading to https://holycrab13.github.io/webid.ttl

Note that Github pages sets the `Content-Type` HTTP response header correctly to `text/turtle`, but needs 3-5 minutes to update on commit/push. See the header here:
`curl -I https://holycrab13.github.io/webid.ttl`

#### Own webspace with Apache and .htaccess
TODO to publish on your own webspace configure Apache and create add the turtle mime-type

### Create the WebID profile document in Turtle syntax

Create a new file and name it `webid.ttl`. Make sure to replace the sequences <YOUR_NAME>, <YOUR_PUBLIC_KEY_MODULUS> and <YOUR_PUBLIC_KEY_EXPONENT> with your respective information.

```
@prefix foaf: <http://xmlns.com/foaf/0.1/> .
@prefix xsd: <http://www.w3.org/2001/XMLSchema#> .
@prefix cert: <http://www.w3.org/ns/auth/cert#> .
@prefix rdfs: <http://www.w3.org/1999/02/22-rdf-syntax-ns#> .

<> a foaf:PersonalProfileDocument ;
   foaf:maker <#this> ;
   foaf:primaryTopic <#this> .

<#this> a foaf:Person ;
   foaf:name "<YOUR_NAME>";
   cert:key [ 
       a cert:RSAPublicKey;
       rdfs:label "<this field is for you to label your key>";
       cert:modulus """<YOUR_PUBLIC_KEY_MODULUS>"""^^xsd:hexBinary;
       cert:exponent <YOUR_PUBLIC_KEY_EXPONENT> ;
      ] . 

``` 



A complete WebId Document can look like this:

```@prefix foaf: <http://xmlns.com/foaf/0.1/> .
@prefix xsd: <http://www.w3.org/2001/XMLSchema#> .
@prefix cert: <http://www.w3.org/ns/auth/cert#> .
@prefix rdfs: <http://www.w3.org/1999/02/22-rdf-syntax-ns#> .

<> a foaf:PersonalProfileDocument ;
   foaf:maker <#this> ;
   foaf:primaryTopic <#this> .

<#this> a foaf:Person ;
   foaf:name "Jan Forberg";
   cert:key [ 
       a cert:RSAPublicKey;
       rdfs:label "made on 23 November 2011 on my laptop";
       cert:modulus "978CD245ADD11F018D321D2A96D5E78BFC2DE540FC2BDFF1A1E46EF3C015A5855155D962DCC538E7BD2927825B2B6FD1048DDE7DC2B2ACDBB9602D87EAA03461A5AEA48DE59709DE9097070146BA8AA3D436780FEA7F328DD3C8DF0D7B359E38B9C2EC37EE2DAD309CDC878FF2CE4DDA48D683C122894D2D892069C77C72FFF977C10BFCE614E243E5B4C919DE6DE5E0D26BFAE9E15385A420E31CDD527388B31B7AA4CAB3B4753F00F7BB58AC4472D5D129B325A539EC02DF5C660311030C452BE05DBB2AF52C0B2D6CBC3843EF0DD87BBCF558F546C82045FDD5D27B1CC1E5C3FC4327391D8727A8E422F45E0F10562A908863A0A93AA3D613B5ADA1A3F29B"^^xsd:hexBinary;
       cert:exponent "65537"^^xsd:integer;
      ] .
```

### Publish the document

Create a new github repository named <YOUR_GITHUB_NAME>.github.io. Replace <YOUR_GITHUB_NAME> with your actual own github account name. Once the repository is created, load your `webid.ttl` to the repository root. After 30 to 60 seconds your WebId document will be accessible under <YOUR_GITHUB_NAME>.github.io/webid.ttl#this. You can verify this by running the URI in your browser.

## Client Certificate
### Generation of the PKCS12 file 

To create a certificate, you first need to generate a certificate file (.cer) file using your private key and a config file. Create a new file and name it `cert_config.cnf`. Paste the following lines into your `cert_config.cnf` and adjust the values accordingly.

```
[req]
default_bits = 2048
prompt = no
default_md = sha256
req_extensions = v3_req
distinguished_name = dn

[ dn ]
# Country Code
C=DE
# State
ST=Saxonia
# Citry
L=Dresden    
# Organization
O=DBpedia
# Organizational Unit
OU=.
# Email Address
emailAddress=jan.forberg@hotmail.de
# Name
CN = Jan Forberg

[ v3_req ]
basicConstraints = CA:FALSE
keyUsage = nonRepudiation, digitalSignature, keyEncipherment
subjectAltName = @alt_names

[ alt_names ]
# Web Id
URI = "https://holycrab13.github.io/webid.ttl#this"
```

Make sure to put your Web Id between quotes, otherwise the fragment identifier will be cropped automatically.
Run the following command to use your private key `private_key.pem` and `cert_config.cnf` to generate a new file `cert.cer`.

```
openssl req -x509 -new -nodes -key private_key.pem -days 3650 -out cert.cer -config cert_config.cnf -extensions v3_req
```

You can validate the contents of `cert.cer` by running

```
openssl x509 -in cert.cer -text
```

Convert your new `cert.cer` to a PKCS12 file using your `private_key.pem` by running the following command.

```
openssl pkcs12 -export -out certificate.pfx -inkey private_key.pem -in cert.cer
```

This generates a new file `certificate.pfx` which can be uploaded to your browser.

### Browser installation

You can upload your `certificate.pfx` to your browser via the settings

* Google Chrome: go to Settings > Advanced > Manage Certificates and Import your certificate file.
* Firefox: go to Preferences > Privacy & Security > Certificates (at bottom) > View Certifiates

 Note: some browsers need a restart. There are plugins that let you switch between webids
 * https://chrome.google.com/webstore/detail/openlink-youid/kbepkemknbihgdmdnfainhmiidoblhee?hl=en
 * https://addons.mozilla.org/en-US/firefox/addon/openlink-youid-ff/

# Usage and Validation

## Retrieval of WebID

`curl -H "Accept: text/turtle" "webid-uri"`

## Syntax validation of WebID profile document (Turtle)

## Content validation of WebID 

## Testing of client certificate and secure connection
http://id.myopenlink.net/ods/webid_demo.html 

## Testing of authentication
http://ods-qa.openlinksw.com/youid
http://linkeddata.uriburner.com/sparql 


# TODO
```
openssl dgst -sha256 -sign ~/.ssh/$private_key  anyFile.txt  | openssl base64 -out anyFile.signed
```



# Background reading
If you are interested in the basic technologies behind WebID and TLS, we recommend to read these documents as a starting point.

## Linked Data and RDF 

## Cryptography and Handshake
* TLS, the successor of SSL: https://en.wikipedia.org/wiki/Transport_Layer_Security
* The TLS handshake: https://en.wikipedia.org/wiki/Transport_Layer_Security#Client-authenticated_TLS_handshake 
* PKCS12 Files (*.pfx;*.p12)
