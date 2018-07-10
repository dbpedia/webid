<?php

// Include TTL parser and convenience webid class
include_once("../WebIdData.php");
include_once("../semsol-arc2/ARC2.php");

// Open Database, select webid from messageboard
$db = new SQLite3('../data/webid.db');
$results = $db->query("SELECT DISTINCT webid FROM comments");

// File name
$file = 'people.js';

// Create JSON content
$content = '{ "types": { "Person": { "pluralLabel": "People" } }, "items": [ ';

// Variable for first comma
$first = TRUE;

// Iterate over all webids
while ($row = $results->fetchArray()) {

  // Parse the webId
  $webid_uri = $row["webid"];

  $rowParser = ARC2::getRDFParser();
  $rowParser->parse($webid_uri);

  // TODO: DO SHACL VALIDATION HERE
  $is_profile_valid = TRUE;


  // SHACL validation was successful
  if($is_profile_valid) {

    // If this entry is not the first, append comma
    if(!$first) {
      $content .= ', ';
    }

    $first = FALSE;

    // Create WebIdData object
    $rowIndex = $rowParser->getSimpleIndex();
    $rowData = new WebIdData($webid_uri, $rowIndex);

    // Append to JSON content
    $content .= '{ ';
    $content .= '"label": "'.$rowData->getFoafName().'",';
    $content .= '"imageURL": "'.$rowData->getFoafImg("https://bulma.io/images/placeholders/128x128.png").'",';
    $content .= '"webidURI": "'.$rowData->getUri().'",';
    $content .= '"type": "Person"';
    $content .= '}';
  }
}

$content .= ' ] }';

// Write content to file
file_put_contents($file, $content);

echo "Done.";
