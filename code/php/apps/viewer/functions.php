<?php

function registerWebId($webIdUri) {

  $db = odbc_connect ("VOS", "dba", "dba");

  if (!$db) {
    return "Keine Verbindung, keine Kekse! ".odbc_errormsg();
  }

  // Remove graph, if exists
  $query = "sparql clear graph <${webIdUri}>";

  if(odbc_exec ($db,  $query) === FALSE) {
    return "Unable to clear graph: ".odbc_errormsg();
  }

  // Load the WebId graph into the triple store
  $query = "sparql load <${webIdUri}> into graph <${webIdUri}>";

  if(odbc_exec ($db,  $query) === FALSE) {
      return "Unable to load graph: ".odbc_errormsg();
  }

  odbc_close ($db);

  return TRUE;
}


function generateExhibitJson($query) {

  $queryUrl = "http://webid.dbpedia.org:8890/sparql?default-graph-uri=&query=".urlencode($query)."&format=xml&timeout=0&debug=on";

  $xmlString =  download_query_result($queryUrl);

  echo $xmlString;

  $xml = new DOMDocument();
  $xml->loadXML($xmlString);

  $xsl = new DOMDocument();
  $xsl->load('sparqlxml2exhibitjson.xsl');

  $proc = new XSLTProcessor;
  $proc->importStyleSheet($xsl);

  return $proc->transformToXml($xml);
}

function download_query_result($queryUrl){
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $queryUrl);
    curl_setopt($ch, CURLOPT_FAILONERROR,1);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION,1);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER,1);
    curl_setopt($ch, CURLOPT_TIMEOUT, 15);
    $retValue = curl_exec($ch);
    curl_close($ch);
    return $retValue;
}
