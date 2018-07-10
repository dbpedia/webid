<?php

/**
 * This file demonstrates the usage of this SHACL processor using a simple example.
 */

require __DIR__ .'/../vendor/autoload.php';

use ShaclPHP\Shacl;

/*
 * --------------------------------------------------------------------------------------------
 * 1. Setup the SHACL core. The Shacl class is a proxy class which handles dependencies initialization
 *    and helps you to using the ShaclPHP backend.
 */
$shacl = new Shacl();


/*
 * --------------------------------------------------------------------------------------------
 * 2. load your SHACL shapes into the store
 */
$shacl->importTurtleRdf('
    @prefix : <http://example/> .
    @prefix foaf: <http://xmlns.com/foaf/0.1/> .
    @prefix rdf: <http://www.w3.org/1999/02/22-rdf-syntax-ns#> .
    @prefix sh: <http://www.w3.org/ns/shacl#> .

    :FoafPersonShape
        rdf:type sh:NodeShape ;
        sh:targetClass foaf:Person ;
        sh:property :FoafPersonShapeFirstName .

    :FoafPersonShapeFirstName
        rdf:type sh:PropertyShape ;
        sh:path foaf:firstName ;
        sh:minCount 1 ;
        sh:maxCount 2 .

');

/*
 * --------------------------------------------------------------------------------------------
 * 3. load the instance data you want to validate later into the store
 *
 * Notice: sure, you can load both your SHACL shapes and instance data at the same time.
 */
$shacl->importTurtleRdf('
    @prefix : <http://example/> .
    @prefix foaf: <http://xmlns.com/foaf/0.1/> .
    @prefix rdf: <http://www.w3.org/1999/02/22-rdf-syntax-ns#> .
    @prefix sh: <http://www.w3.org/ns/shacl#> .

    :person1 rdf:type foaf:Person ;
        foaf:firstName "Helmut" .
');

/*
 * --------------------------------------------------------------------------------------------
 * 4. Validate instance data against SHACL shapes
 */
$result = $shacl->check(); // will return an instance of ValidationReport

/*
 * --------------------------------------------------------------------------------------------
 * 5. Handle result
 */
// data ok
if ($result->conforms()) {
    echo PHP_EOL . 'Data valid.';

// data invalid, show ValidationResults
} else {
    var_dump($result->getResults());
}

echo PHP_EOL . PHP_EOL;
