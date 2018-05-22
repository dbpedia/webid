#/bin/sh 
rm -r /var/www/webid.dbpedia.org/voc
mv voc /var/www/webid.dbpedia.org/voc

cd /var/www/webid.dbpedia.org/voc

rapper -i turtle -o rdfxml webid.ttl > webid.owl



