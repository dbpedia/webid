<?php
// config

// shacl service
$base = "http://localhost:8080/shacl/validate"; 

// schemas
$schemas="https://raw.githubusercontent.com/dbpedia/webid/master/voc/webid-shacl.ttl,foaf";


// rewriting the remaining parameters
if(!isset($_GET['d'])|| $_GET['d']===0){
	echo "<pre>Usage:
Give the URI of the dataset to be validated with 'd', eg: ".$_SERVER['REQUEST_URI']."?d=http://kurzum.net/webid.ttl
'o' defines output format,  either &o=html or &o=ttl
'r' defines the different test reports, either &r=shacl or &r=aggregate
"	;
	die;		
}else {
	$dataseturi = "&d=".urlencode($_GET['d']) ;

}
$output = "&o=html";
if (isset($_GET['o'])){
$output = "&o=".$_GET['o'];
}
$format = "&r=shacl";
if (isset($_GET['r'])){
$format = "&r=".$_GET['r'];
}

//******************************
// building the uri

//curl  http://localhost:8080/shacl/validate --data-urlencode "d=http://kurzum.net/webid.ttl" --data-urlencode "s=https://raw.githubusercontent.com/dbpedia/webid/master/voc/webid-shacl.ttl" -d "A" -d "v" -d "r=shacl"

$fixed_vars="?s=".urlencode($schemas)."&A&v&" ;
$uri=$base.$fixed_vars.$dataseturi.$output.$format;


$ch = curl_init();
curl_setopt($ch,CURLOPT_URL,$uri); 
//return the transfer as a string
curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
// $output contains the output string
$output = curl_exec($ch);
echo $output ;
// close curl resource to free up system resources
curl_close($ch);      
//echo $uri; 
