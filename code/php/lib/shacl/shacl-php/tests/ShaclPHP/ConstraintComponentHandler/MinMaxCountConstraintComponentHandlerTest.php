<?php

namespace Tests\ShaclPHP\ConstraintComponentHandler;

use ShaclPHP\ConstraintComponentHandler\MinMaxCountConstraintComponentHandler;
use Tests\ShaclPHP\TestCase;

class MinMaxCountConstraintComponentHandlerTest extends TestCase
{
    public function setUp()
    {
        parent::setUp();

        $this->fixture = new MinMaxCountConstraintComponentHandler(
            $this->nodeFactory,
            $this->commonNamespaces,
            $this->resourceGuyHelper,
            $this->constraintComponentHandlerFactory
        );
    }

    /*
     * Tests for handle
     */

    // OK
    public function testMaxCountHandle()
    {
        $foaf = $this->commonNamespaces->getUri('foaf');
        $sh = $this->commonNamespaces->getUri('sh');

        /*
         * SHACL constraint
         */
        $this->importTurtle(file_get_contents($this->examplesPath . 'example-maxCount.ttl'));
        $shapes = $this->shaclEngine->getNodeShapes();

        /*
         * instance data
         */
        $this->importTurtle('
            @prefix foaf: <'. $this->commonNamespaces->getUri('foaf') .'> .

            <http://person/1> a foaf:Person ;
                foaf:firstName "helmut" .
        ');

        $this->assertEquals(0, count($this->fixture->handle($shapes[0])));
    }

    // OK
    public function testMaxCountHandle2()
    {
        $foaf = $this->commonNamespaces->getUri('foaf');
        $sh = $this->commonNamespaces->getUri('sh');

        /*
         * SHACL constraint
         */
        $this->importTurtle(file_get_contents($this->examplesPath . 'example-maxCount.ttl'));
        $shapes = $this->shaclEngine->getNodeShapes();

        /*
         * instance data
         */
        $this->importTurtle('
            @prefix foaf: <'. $this->commonNamespaces->getUri('foaf') .'> .

            <http://person/1> a foaf:Person .
        ');

        $this->assertEquals(0, count($this->fixture->handle($shapes[0])));
    }

    // Violation
    public function testMaxCountViolation()
    {
        $foaf = $this->commonNamespaces->getUri('foaf');
        $sh = $this->commonNamespaces->getUri('sh');

        /*
         * SHACL constraint
         */
        $this->importTurtle(file_get_contents($this->examplesPath . 'example-maxCount.ttl'));
        $shapes = $this->shaclEngine->getNodeShapes();

        /*
         * instance data
         */
        $this->importTurtle('
            @prefix foaf: <'. $this->commonNamespaces->getUri('foaf') .'> .

            <http://person/1> a foaf:Person ;
                foaf:firstName "helmut" ;
                foaf:firstName "ulf" .
        ');

        $this->assertEquals(1, count($this->fixture->handle($shapes[0])));
    }

    // OK
    public function testMinCountHandle()
    {
        $foaf = $this->commonNamespaces->getUri('foaf');
        $sh = $this->commonNamespaces->getUri('sh');

        /*
         * SHACL constraint
         */
        $this->importTurtle(file_get_contents($this->examplesPath . 'example-minCount.ttl'));
        $shapes = $this->shaclEngine->getNodeShapes();

        /*
         * instance data
         */
        $this->importTurtle('
            @prefix foaf: <'. $this->commonNamespaces->getUri('foaf') .'> .

            <http://person/1> a foaf:Person ;
                foaf:firstName "helmut" .
        ');

        $this->assertEquals(0, count($this->fixture->handle($shapes[0])));
    }

    // Violation
    public function testMinCountHandleViolation()
    {
        $foaf = $this->commonNamespaces->getUri('foaf');
        $sh = $this->commonNamespaces->getUri('sh');

        /*
         * SHACL constraint
         */
        $this->importTurtle(file_get_contents($this->examplesPath . 'example-minCount.ttl'));
        $shapes = $this->shaclEngine->getNodeShapes();

        /*
         * instance data
         */
        $this->importTurtle('
            @prefix foaf: <'. $this->commonNamespaces->getUri('foaf') .'> .

            <http://person/1> a foaf:Person .
        ');

        $this->assertEquals(1, count($this->fixture->handle($shapes[0])));
    }

    // OK
    public function testMinMaxCountHandle()
    {
        $foaf = $this->commonNamespaces->getUri('foaf');
        $sh = $this->commonNamespaces->getUri('sh');

        /*
         * SHACL constraint
         */
        $this->importTurtle(file_get_contents($this->examplesPath . 'example-min-and-maxCount.ttl'));
        $shapes = $this->shaclEngine->getNodeShapes();

        /*
         * instance data
         */
        $this->importTurtle('
            @prefix foaf: <'. $this->commonNamespaces->getUri('foaf') .'> .

            <http://person/1> a foaf:Person ;
                foaf:firstName "helmut" ;
                foaf:firstName "ulf" .
        ');

        $this->assertEquals(0, count($this->fixture->handle($shapes[0])));
    }

    // Violation
    public function testMinMaxCountHandleViolation()
    {
        $foaf = $this->commonNamespaces->getUri('foaf');
        $sh = $this->commonNamespaces->getUri('sh');

        /*
         * SHACL constraint
         */
        $this->importTurtle(file_get_contents($this->examplesPath . 'example-min-and-maxCount.ttl'));
        $shapes = $this->shaclEngine->getNodeShapes();

        /*
         * instance data
         */
        $this->importTurtle('
            @prefix foaf: <'. $this->commonNamespaces->getUri('foaf') .'> .

            <http://person/1> a foaf:Person ;
                foaf:firstName "helmut" ;
                foaf:firstName "ulf" ;
                foaf:firstName "--to-many" .
        ');

        $this->assertEquals(1, count($this->fixture->handle($shapes[0])));
    }
}
