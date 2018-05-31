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

# Example
This is Jan's WebID: http://holycrab13.github.io/webid.ttl#this
If you open it in a browser or `curl` it the `#this` is ignored and the full turtle file is retrieved. In the turtle file you can find a section with additional information about Jan as well as his public key:
```
<#this> a foaf:Person ;
   foaf:name "Jan Forberg";
   cert:key [ 
       a cert:RSAPublicKey;
       rdfs:label "made on 23 November 2011 on my laptop";
       cert:modulus """00:[....]9b"""^^xsd:hexBinary;
       cert:exponent 65537 ;
      ] .
``` 

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
### Modulus and exponent

## WebID and WebID profile document
### Choose the URI
### Create the Turtle document
### Publish the document

## Client Certificate
### Generation of the PKCS12 file 
### Browser installation

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
