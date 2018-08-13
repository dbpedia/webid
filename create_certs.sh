#!/usr/bin/env bash

# WebId information
echo "Please enter your WebId:"
read -p "WebId: " webid_uri
read -p "Certificate Label (i.e. 'Generated on Laptop'): " webid_cert_label

# Certificate information
echo "Please enter your Certificate information:"
echo "If you enter '.', the field will be left blank."
read -p "Common Name: " cert_name
read -p "Email Address: " cert_email
read -p "Country Name (2 letter code): " cert_country
read -p "State or Province Name (full name): " cert_state
read -p "Locality Name (eg, city): " cert_city
read -p "Organization Name (eg, company): " cert_org
read -p "Organizational Unit Name (eg, section): " cert_org_unit

# File information
echo "Please enter your file information"
read -p "Certificate File Suffix (certificate file will be named 'certificate_[suffix]'): " file_suffix


echo "Generating Private Key"
openssl genpkey -algorithm  RSA -out private_key_$file_suffix.pem -pkeyopt rsa_keygen_bits:2048
echo "Generating Public Key"
openssl rsa -pubout -in private_key_$file_suffix.pem -out public_key_$file_suffix.pem

pubout=$(openssl rsa -pubin -inform PEM -text -noout < public_key_$file_suffix.pem)

IFS=$'\n'
arrIN=($pubout)
unset IFS

modulus=""
exponent=""
k=0
length=${#arrIN[@]}

for i in "${arrIN[@]}"; do
    # process "$i"
    if (( k > 1 )) && (( k < length - 1 ))
    then
      modulus=$modulus$i
    fi

    if (( k == length -1 ))
    then
      exponent=$i
    fi

    k=$((k+1))
done

modulus=${modulus//[: ]/}
modulus="${modulus:2}"
modulus="${modulus^^}"

expArray=($exponent)
exponent="${expArray[1]}"

echo "Generating Certificate Config"
cat > cert_$file_suffix.config <<EOL
[req]
default_bits = 2048
prompt = no
default_md = sha256
req_extensions = v3_req
distinguished_name = dn
[ dn ]
# Country Code
C=${cert_country}
# State
ST=${cert_state}
# Citry
L=${cert_city}
# Organization
O=${cert_org}
# Organizational Unit
OU=${cert_org_unit}
# Email Address
emailAddress = "${cert_email}"
# Name
CN=${cert_name}

[ v3_req ]
basicConstraints = CA:FALSE
keyUsage = nonRepudiation, digitalSignature, keyEncipherment
subjectAltName = @alt_names

[ alt_names ]
# Web Id
URI = "${webid_uri}"
EOL

echo "Generating x509 Certificate"
openssl req -x509 -new -nodes -key private_key_$file_suffix.pem -days 3650 -out x509_$file_suffix.cer -config cert_${file_suffix}.config -extensions v3_req

echo "Generating PKCS12 Certificate"
openssl pkcs12 -export -out certificate_$file_suffix.pfx -inkey private_key_$file_suffix.pem -in x509_$file_suffix.cer

# additional format for curl 
echo "Converting PKCS12 file to PEM (used by curl)"
openssl pkcs12 -in certificate_$file_suffix.pfx -out certificate_$file_suffix.pem

# additional format for java
echo "Converting Private Key to DER (used by java and Databus Maven Plugin)"
openssl pkcs8 -topk8 -inform PEM -outform DER -in private_key_$file_suffix.pem -out private_key_$file_suffix.der -nocrypt

echo "Generating WebId Cert Content"
cat > webid_cert_$file_suffix.txt <<EOL
cert:key [
      a cert:RSAPublicKey;
      rdfs:label "${webid_cert_label}";
      cert:modulus "${modulus}"^^xsd:hexBinary;
      cert:exponent "${exponent}"^^xsd:nonNegativeInteger
     ] .
EOL


echo "SUCCESS! You can import your certificate_$file_suffix.pfx file into your browser. Insert the content of webid_cert_$file_suffix.txt into your WebId document as shown in the documentation at https://github.com/dbpedia/webid."
