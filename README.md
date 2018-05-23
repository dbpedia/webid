# WebID
WebID Creation and Vocabulary

# What is a WebID?
A WebID is a novel way to identify and authenticate users and organisations on the Web with simple methods. The main advantages are:
* people and organisations become the main authority for their data
* you do not need passwords any more as authentication is done with your own secure key

In comparison to any other service like Facebook, LinkedIn or OpenID you are the owner of your data and of your secure key. 
With the WebID file and your key you can create accounts, delete accounts and establish secure connections. 


# Further reading
While we try to summarize here the basic points, we would like to hint you at the following pages:
* 

# How does it work?
The four rules of WebID:
1.  The WebID is a file that provides a machine-readable description of a person or an organisation.
2.  The WebID file is editable only by its owner or the owning organisation (to prevent fraud). 
3.  The WebID file must be reachable via HTTP(S), so any application can discover and verify it.
4.  One or several public key(s) and a hash of email address are embedded in the WebID. The owner of the WebID can therefore prove his ownership of the email and the WebID with the corresponding private key during authentification. 

# How to generate a WebID (Rule 1)

WebID is using the FOAF Vocabulary in RDF. It has many different serialisation formats. The easiest to write is Turtle, which can be written manually. JSON-LD and RDFXML are good alternatives. 

# How to publish/discover a WebID (Rule 2 and 3)

## Discovery
There are three main ways to discover a WebID:
1. HTTP Response Header
2. Link Rel in HTML meta
3. A third party links your WebID

## Publication

1. Put the file on own webserver
2. Use Github.io

# How to add your public key and other security features (Rule 4)
## SHA 1 and SHA 256 sum of email

* The FOAF property foaf:mbox_sha1sum 'should' be kept for downward compatibility.
* The property webid:mbox_sha256sum 'must' be used in all DBpedia related activities to verify email against a WebID profile 

Generate with `echo -n peter@example.org | sha1sum` resulting in `<#me> foaf:mbox_sha1sum "728b61829d8052a89a111991707091dec09fe190" `
Generate with `echo -n peter@example.org | sha256sum` resulting in `<#me> webid:mbox_sha256sum "97cb7349a88f41fa1338c41124dcfce6017a02e6b58ee7022799e650e817fb5a" `



```
openssl dgst -sha256 -sign ~/.ssh/$our_private_key  anyFile.txt  | openssl base64 -out anyFile.signed
```
