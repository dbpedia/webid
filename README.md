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

```
openssl genpkey -algorith, RSA -out private_key.pem -pkeyopt rsa_keygen_bits:2048
openssl rsa -pubout -in private_key.pem -out public_key.pem
```
This will create two files, private_key.pem and public_key.pem

### Modulus and exponent

To output the modulus and exponent, run
```
openssl rsa -pubin -inform PEM -text -noout < public.key
```
## WebID and WebID profile document
### Choose the URI

We currently recommend using github pages as a hosting service for your WebId, since it is freely available. 
Your WebId will then be <YOUR_GITHUB_NAME>.github.io/webid.ttl#this (see example above)

### Create the Turtle document

You can setup your basic turtle profile document as follows

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
       rdfs:label "made on 23 November 2011 on my laptop";
       cert:modulus """<YOUR_PUBLIC_KEY_MODULUS>"""^^xsd:hexBinary;
       cert:exponent <YOUR_PUBLIC_KEY_EXPONENT> ;
      ] . 

``` 

Create a new file and name it `webid.ttl`. Post the code above and make sure to replace the sequences <YOUR_NAME>, <YOUR_PUBLIC_KEY_MODULUS> and <YOUR_PUBLIC_KEY_EXPONENT> with your respective information.

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
       cert:modulus """00:97:8c:d2:45:ad:d1:1f:01:8d:32:1d:2a:96:d5:
    e7:8b:fc:2d:e5:40:fc:2b:df:f1:a1:e4:6e:f3:c0:
    15:a5:85:51:55:d9:62:dc:c5:38:e7:bd:29:27:82:
    5b:2b:6f:d1:04:8d:de:7d:c2:b2:ac:db:b9:60:2d:
    87:ea:a0:34:61:a5:ae:a4:8d:e5:97:09:de:90:97:
    07:01:46:ba:8a:a3:d4:36:78:0f:ea:7f:32:8d:d3:
    c8:df:0d:7b:35:9e:38:b9:c2:ec:37:ee:2d:ad:30:
    9c:dc:87:8f:f2:ce:4d:da:48:d6:83:c1:22:89:4d:
    2d:89:20:69:c7:7c:72:ff:f9:77:c1:0b:fc:e6:14:
    e2:43:e5:b4:c9:19:de:6d:e5:e0:d2:6b:fa:e9:e1:
    53:85:a4:20:e3:1c:dd:52:73:88:b3:1b:7a:a4:ca:
    b3:b4:75:3f:00:f7:bb:58:ac:44:72:d5:d1:29:b3:
    25:a5:39:ec:02:df:5c:66:03:11:03:0c:45:2b:e0:
    5d:bb:2a:f5:2c:0b:2d:6c:bc:38:43:ef:0d:d8:7b:
    bc:f5:58:f5:46:c8:20:45:fd:d5:d2:7b:1c:c1:e5:
    c3:fc:43:27:39:1d:87:27:a8:e4:22:f4:5e:0f:10:
    56:2a:90:88:63:a0:a9:3a:a3:d6:13:b5:ad:a1:a3:
    f2:9b"""^^xsd:hexBinary;
       cert:exponent 65537 ;
      ] .
```

### Publish the document

Create a new github repository named <YOUR_GITHUB_NAME>.github.io. Replace <YOUR_GITHUB_NAME> with your actual own github account name. Once the repository is created, load your `webid.ttl` to the repository root. After 30 to 60 seconds your WebId document will be accessible under <YOUR_GITHUB_NAME>.github.io/webid.ttl#this. You can verify this by running the URI in your browser.

## Client Certificate
### Generation of the PKCS12 file 

To create a certificate, you first need to generate a certificate file (.cer) file using your private key and a config file. Create a new file and name it `cert_config.cnf`. Paste the following lines into your `cert_config.cnf` and adjust the values accordingly.

``
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
DNS = holycrab13.github.io/webid.ttl#this
``

Run the following command to use your private key `private_key.pem` and `cert_config.cnf` to generate a new file `cert.cer`.
``
openssl req -x509 -new -nodes -key private_key.pem -days 3650 -out cert.cer -config cert_config.cnf -extensions v3_req
``

You can validate the contents of `cert.cer` by running

``
openssl x509 -in cert.cer -text
``

Convert your new `cert.cer` to a PKCS12 file using your `private_key.pem` by running the following command.

``
openssl pkcs12 -export -out certificate.pfx -inkey private_key.pem -in cert.cer
``

This generates a new file `certificate.pfx` which can be uploaded to your browser.

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
