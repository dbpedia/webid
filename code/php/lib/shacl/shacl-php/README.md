# shacl-php

| Build Status                                                             | Code Coverage                                                                                                                                              |
|:-------------------------------------------------------------------------|:-----------------------------------------------------------------------------------------------------------------------------------------------------------|
| ![Build Status](https://travis-ci.org/k00ni/shacl-php.svg?branch=master) | [![Coverage Status](https://coveralls.io/repos/github/k00ni/shacl-php/badge.svg?branch=master)](https://coveralls.io/github/k00ni/shacl-php?branch=master) |

## Helpful links

Specification: https://www.w3.org/TR/shacl/

SHACL playground (good when developing/testing shapes): http://shacl.org/playground/

Validating RDF Book (HTML): http://book.validatingrdf.com/

## General restrictions

### No RDF lists support

Our basic in-memory store implementation does not support RDF lists.

## Example

The following example demonstrates the usage of ShaclPHP and is based on this [example file](examples/1-sh-or.php). It shows

* how to init the SHACL core,
* load SHACL shapes and load instance data and
* how data validation is made.

```php
<?php

require __DIR__ .'/../vendor/autoload.php';

use ShaclPHP\Shacl;

/*
 * 1. Setup the SHACL core. The Shacl class is a proxy class which handles dependencies initialization
 *    and helps you using the ShaclPHP backend.
 */
$shacl = new Shacl();


/*
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
 * 4. Validate instance data against SHACL shapes
 */
$result = $shacl->check(); // will return an instance of ValidationReport

/*
 * 5. Handle result
 */
// data ok
if ($result->conforms()) {
    echo 'Data valid.';

// data invalid, show ValidationResults
} else {
    var_dump($result->getResults());
    /*
        in case data is invalid, the results array contains detailed information.

        EXAMPLE:

        array(1) {
            [0] =>
            class Shacl\ValidationResult#136 (8) {
                protected $focusNode =>
                class Saft\Rdf\NamedNodeImpl#147 (1) {
                  protected $uri =>
                  string(22) "http://example/person1"
                }
                protected $resultMessages =>
                array(1) {
                  [0] =>
                  string(99) "http://example/person1 has to have property http://xmlns.com/foaf/0.1/firstName at least 1 time(s)."
                }
                protected $resultPath =>
                class Saft\Rdf\NamedNodeImpl#74 (1) {
                  protected $uri =>
                  string(35) "http://xmlns.com/foaf/0.1/firstName"
                }
                protected $resultSeverity =>
                class Shacl\Severity#138 (1) {
                  protected $type =>
                  string(12) "sh:Violation"
                }
                protected $sourceConstraintComponent =>
                class Saft\Rdf\NamedNodeImpl#144 (1) {
                  protected $uri =>
                  string(33) "sh:MinMaxCountConstraintComponent"
                }
                protected $sourceShape =>
                class Saft\Rdf\NamedNodeImpl#137 (1) {
                  protected $uri =>
                  string(30) "http://example/FoafPersonShape"
                }
                protected $value =>
                NULL
                protected $supportedContraintComponents =>
                array(4) {
                  ...
                }
            }
        }
    */
}
```

## SHACL support

Based on version: [**W3C Recommendation 20 July 2017**](https://www.w3.org/TR/2017/REC-shacl-20170720/)


### sh:and

Basic support for `sh:and`. Nested structures are not supported yet.

**Notice**: No RDF list support, therefore using a simple triple list.

Example:

```
:NodeShape
    rdf:type sh:NodeShape ;
    sh:targetClass foaf:Person ;
    sh:and [
            sh:path foaf:firstName ;
            sh:minCount 1 ;
        ],
        [
            sh:path foaf:givenName ;
            sh:minCount 1 ;
        ] .
```

Not suported are nested structures such as:

```
ex:RectangleWithArea
  rdf:type sh:NodeShape ;
  sh:and (
      [
        sh:property [
            sh:path ex:height ;
            sh:minCount 1 ;
          ] ;
        sh:property [
            sh:path ex:width ;
            sh:minCount 1 ;
          ] ;
      ]
      [
        sh:property [
            sh:path ex:area ;
            sh:minCount 1 ;
          ] ;
      ]
    ) ;
```

### sh:hasValue

Example:

```
:NodeShape
    rdf:type sh:NodeShape ;
    sh:targetClass foaf:Person ;
    sh:property :PropertyShape .

:PropertyShape
    rdf:type sh:PropertyShape ;
    sh:path foaf:gender ;
    sh:hasValue "female" .
```

### sh:minCount and sh:maxCount

Support for either `sh:minCount` or `sh:maxCount`, or both of them.

Example:

```
:NodeShape
    rdf:type sh:NodeShape ;
    sh:targetClass foaf:Person ;
    sh:property :PropertyShape .

:PropertyShape
    rdf:type sh:PropertyShape ;
    sh:path foaf:firstName ;
    sh:minCount 1 ;
    sh:maxCount .
```

### sh:or

Basic support for `sh:or`. Nested structures are not supported yet.

**Notice**: No RDF list support, therefore using a simple triple list.

Example:

```
:NodeShape
    rdf:type sh:NodeShape ;
    sh:targetClass foaf:Person ;
    sh:or [
            sh:path foaf:firstName ;
            sh:minCount 1 ;
        ],
        [
            sh:path foaf:givenName ;
            sh:minCount 1 ;
        ] .
```

Not suported are nested structures such as:

```
ex:RectangleWithArea
  rdf:type sh:NodeShape ;
  sh:or (
      [
        sh:property [
            sh:path ex:height ;
            sh:minCount 1 ;
          ] ;
        sh:property [
            sh:path ex:width ;
            sh:minCount 1 ;
          ] ;
      ]
      [
        sh:property [
            sh:path ex:area ;
            sh:minCount 1 ;
          ] ;
      ]
    ) ;
```
