# WebID
WebID registration and Vocabulary

# Specification

## SHA 256 sum of email

* The FOAF property foaf:mbox_sha1sum 'should' be kept for download compatibility.
* The property webid:mbox_sha256sum 'must' be used in all DBpedia related activities to verify email against a WebID profile 

Generate with `echo -n peter@example.org | sha256sum` resulting in `<#me> webid:mbox_sha256sum "97cb7349a88f41fa1338c41124dcfce6017a02e6b58ee7022799e650e817fb5a" `



# Ubuntu + OpenSSL




```
openssl dgst -sha256 -sign ~/.ssh/$our_private_key  anyFile.txt  | openssl base64 -out anyFile.signed
```
