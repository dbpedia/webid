<?php

namespace Tests\ShaclPHP;

use ShaclPHP\ConstraintComponentHandlerFactory;
use ShaclPHP\ShaclEngine;

class ShaclEngineTest extends TestCase
{
    public function setUp()
    {
        parent::setUp();

        $this->fixture = new ShaclEngine(
            $this->rdfHelpers,
            $this->commonNamespaces,
            $this->nodeFactory,
            $this->store,
            $this->resourceGuyHelper,
            $this->constraintComponentHandlerFactory
        );
    }

    /*
     * Tests for checkGraph (separated by constraint components)
     */

    // sh:and

    // conforms = true
    public function testCheckAnd()
    {
        // load content of an example file into the test graph
        $this->importTurtle(file_get_contents($this->examplesPath . 'example-and.ttl'));

        $this->importTurtle('
            @prefix foaf: <'. $this->commonNamespaces->getUri('foaf') .'> .

            <http://female/1> a foaf:Person ;
                foaf:firstName "foo" ;
                foaf:lastName "bar" .
        ');

        $this->assertTrue($this->fixture->check()->conforms());
    }

    // conforms = false
    public function testCheckAndViolation()
    {
        // load content of an example file into the test graph
        $this->importTurtle(file_get_contents($this->examplesPath . 'example-and.ttl'));

        $this->importTurtle('
            @prefix foaf: <'. $this->commonNamespaces->getUri('foaf') .'> .

            <http://man/1> a foaf:Person ;
                foaf:givenName "not the right name" .
        ');

        $this->assertFalse($this->fixture->check()->conforms());
    }

    // sh:hasValue

    // conforms = true
    public function testCheckHasValue()
    {
        // load content of an example file into the test graph
        $this->importTurtle(file_get_contents($this->examplesPath . 'example-hasValue.ttl'));

        $this->importTurtle('
            @prefix foaf: <'. $this->commonNamespaces->getUri('foaf') .'> .

            <http://female/1> a foaf:Person ;
                foaf:gender "female" .

            <http://female/2> a foaf:Person ;
                foaf:gender "female" .
        ');

        $this->assertTrue($this->fixture->check()->conforms());
    }

    // conforms = false
    public function testCheckHasValueViolation()
    {
        // load content of an example file into the test graph
        $this->importTurtle(file_get_contents($this->examplesPath . 'example-hasValue.ttl'));

        $this->importTurtle('
            @prefix foaf: <'. $this->commonNamespaces->getUri('foaf') .'> .

            <http://man/1> a foaf:Person ;
                foaf:gender "man" .
        ');

        $this->assertFalse($this->fixture->check()->conforms());
    }

    // sh:maxCount

    // conforms = true
    public function testCheckMaxCount()
    {
        // load content of an example file into the test graph
        $this->importTurtle(file_get_contents($this->examplesPath . 'example-maxCount.ttl'));

        $this->importTurtle('
            @prefix foaf: <'. $this->commonNamespaces->getUri('foaf') .'> .
            @prefix sh: <'. $this->commonNamespaces->getUri('sh') .'> .

            <http://person1/> a foaf:Person ;
                foaf:firstName "helmut" ;
                foaf:lastName "meiner" .
        ');

        $this->assertTrue($this->fixture->check()->conforms());
    }

    // conforms = false
    public function testCheckMaxCountViolation()
    {
        // load content of an example file into the test graph
        $this->importTurtle(file_get_contents($this->examplesPath . 'example-maxCount.ttl'));

        $this->importTurtle('
            @prefix foaf: <'. $this->commonNamespaces->getUri('foaf') .'> .
            @prefix sh: <'. $this->commonNamespaces->getUri('sh') .'> .

            <http://person1/> a foaf:Person ;
                foaf:firstName "helmut" ;
                foaf:firstName "ulf" .
        ');

        $this->assertFalse($this->fixture->check()->conforms());
    }

    // sh:minCount

    // conforms = true
    public function testCheckMinCount()
    {
        // load content of an example file into the test graph
        $this->importTurtle(file_get_contents($this->examplesPath . 'example-minCount.ttl'));

        $this->importTurtle('
            @prefix foaf: <'. $this->commonNamespaces->getUri('foaf') .'> .
            @prefix sh: <'. $this->commonNamespaces->getUri('sh') .'> .

            <http://person1/> a foaf:Person ;
                foaf:firstName "helmut" ;
                foaf:firstName "ulf" ;
                foaf:lastName "meiner" .
        ');

        $report = $this->fixture->check();
        $this->assertTrue($report->conforms());
    }

    // conforms = false
    public function testCheckMinCountViolation()
    {
        // load content of an example file into the test graph
        $this->importTurtle(file_get_contents($this->examplesPath . 'example-minCount.ttl'));

        $this->importTurtle('
            @prefix foaf: <'. $this->commonNamespaces->getUri('foaf') .'> .
            @prefix sh: <'. $this->commonNamespaces->getUri('sh') .'> .

            <http://person1/> a foaf:Person .
        ');

        $report = $this->fixture->check();
        $this->assertFalse($report->conforms());
    }

    // sh:or

    // conforms = true
    public function testCheckOr()
    {
        // load content of an example file into the test graph
        $this->importTurtle(file_get_contents($this->examplesPath . 'example-or.ttl'));

        $this->importTurtle('
            @prefix foaf: <'. $this->commonNamespaces->getUri('foaf') .'> .

            <http://female/1> a foaf:Person ;
                foaf:firstName "foo" ;
                foaf:lastName "bar" .
        ');

        $this->assertTrue($this->fixture->check()->conforms());
    }

    // conforms = false
    public function testCheckOrViolation()
    {
        // load content of an example file into the test graph
        $this->importTurtle(file_get_contents($this->examplesPath . 'example-or.ttl'));

        $this->importTurtle('
            @prefix foaf: <'. $this->commonNamespaces->getUri('foaf') .'> .

            <http://man/1> a foaf:Person ;
                foaf:givenName "first" , "second" , "third" .
        ');

        $this->assertFalse($this->fixture->check()->conforms());
    }

    /*
     * Tests for getNodeShapes
     */

    public function testGetNodeShapes()
    {
        // load content of an example file into the test graph
        $this->importTurtle(file_get_contents($this->examplesPath . 'example-minCount.ttl'));

        $foaf = $this->commonNamespaces->getUri('foaf');
        $rdf = $this->commonNamespaces->getUri('rdf');
        $sh = $this->commonNamespaces->getUri('sh');

        $exceptedShape = array(
            '_idUri' => $this->nodeFactory->createNamedNode('http://shacl-php/example-minCount/FoafPersonShape'),
            $rdf . 'type' => array('_idUri' => $this->nodeFactory->createNamedNode($sh . 'NodeShape')),
            $sh . 'targetClass' => array('_idUri' => $this->nodeFactory->createNamedNode($foaf . 'Person')),
            $sh . 'property' => array(
                '_idUri' => $this->nodeFactory->createNamedNode('http://shacl-php/example-minCount/FoafPersonShapeFirstName'),
                $rdf . 'type' => $this->nodeFactory->createNamedNode($sh . 'PropertyShape'),
                $sh . 'path' => $this->nodeFactory->createNamedNode($foaf . 'firstName'),
                $sh . 'minCount' => $this->nodeFactory->createLiteral('1', 'http://www.w3.org/2001/XMLSchema#integer'),
            ),
        );

        $this->assertEquals(
            array($exceptedShape),
            array(
                $this->fixture->getNodeShapes()[0]->getArrayCopy()
            )
        );
    }

    // focus lies on the sh:or property and its objects
    public function testGetNodeShapesOrConstraint()
    {
        $this->importTurtle(file_get_contents($this->examplesPath . 'example-or.ttl'));

        $foaf = $this->commonNamespaces->getUri('foaf');
        $rdf = $this->commonNamespaces->getUri('rdf');
        $sh = $this->commonNamespaces->getUri('sh');

        $result = $this->fixture->getNodeShapes();

        /*
         * check objects of sh:or property
         */
        $this->assertTrue(isset($result[0]['sh:or']));
        $this->assertEquals(2, count($result[0]['sh:or']));

        // entry 1/2
        $this->assertEquals(
            array(
                'http://www.w3.org/ns/shacl#path' => $this->nodeFactory->createNamedNode('http://xmlns.com/foaf/0.1/firstName'),
                'http://www.w3.org/ns/shacl#minCount' => $this->nodeFactory->createLiteral('1')
            ),
            $result[0]['sh:or'][0]->getArrayCopy()
        );

        // entry 2/2
        $this->assertEquals(
            array(
                'http://www.w3.org/ns/shacl#path' => $this->nodeFactory->createNamedNode('http://xmlns.com/foaf/0.1/givenName'),
                'http://www.w3.org/ns/shacl#maxCount' => $this->nodeFactory->createLiteral('1')
            ),
            $result[0]['sh:or'][1]->getArrayCopy()
        );
    }

    // tests gathering of property shapes which are attached as blank nodes
    public function testGetNodeShapesPropertyShapesAsBlankNode()
    {
        // load content of an example file into the test graph
        $this->importTurtle(file_get_contents($this->examplesPath . 'example-propertyShape-as-BlankNode.ttl'));

        $this->assertEquals(1, count($this->fixture->getNodeShapes()));
        $this->assertEquals(
            array(
                '_idUri' => $this->nodeFactory->createNamedNode('http://shacl-php/example-propertyShape-as-BlankNode/NodeShape'),
                'http://www.w3.org/1999/02/22-rdf-syntax-ns#type' => array(
                    '_idUri' => $this->nodeFactory->createNamedNode('http://www.w3.org/ns/shacl#NodeShape'),
                ),
                'http://www.w3.org/ns/shacl#targetClass' => array(
                    '_idUri' => $this->nodeFactory->createNamedNode('http://xmlns.com/foaf/0.1/Person'),
                ),
                'http://www.w3.org/ns/shacl#property' => array(
                    array(
                        '_idUri' => $this->nodeFactory->createBlankNode('b0'),
                        'http://www.w3.org/1999/02/22-rdf-syntax-ns#type'
                            => $this->nodeFactory->createNamedNode('http://www.w3.org/ns/shacl#PropertyShape'),
                        'http://www.w3.org/ns/shacl#path' => $this->nodeFactory->createNamedNode('http://xmlns.com/foaf/0.1/firstName'),
                        'http://www.w3.org/ns/shacl#minCount' => $this->nodeFactory->createLiteral('1', 'http://www.w3.org/2001/XMLSchema#integer'),
                    ),
                    array(
                        '_idUri' => $this->nodeFactory->createBlankNode('b1'),
                        'http://www.w3.org/1999/02/22-rdf-syntax-ns#type'
                            => $this->nodeFactory->createNamedNode('http://www.w3.org/ns/shacl#PropertyShape'),
                        'http://www.w3.org/ns/shacl#path' => $this->nodeFactory->createNamedNode('http://xmlns.com/foaf/0.1/givenName'),
                        'http://www.w3.org/ns/shacl#minCount' => $this->nodeFactory->createLiteral('1', 'http://www.w3.org/2001/XMLSchema#integer'),
                    ),
                )
            ),
            $this->fixture->getNodeShapes()[0]->getArrayCopy()
        );
    }
}
