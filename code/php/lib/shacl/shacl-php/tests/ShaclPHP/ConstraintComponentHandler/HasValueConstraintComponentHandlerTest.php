<?php

namespace Tests\ShaclPHP\ConstraintComponentHandler;

use ShaclPHP\ConstraintComponentHandler\HasValueConstraintComponentHandler;
use Tests\ShaclPHP\TestCase;

class HasValueConstraintComponentHandlerTest extends TestCase
{
    public function setUp()
    {
        parent::setUp();

        $this->fixture = new HasValueConstraintComponentHandler(
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
    public function testHandle()
    {
        $foaf = $this->commonNamespaces->getUri('foaf');
        $sh = $this->commonNamespaces->getUri('sh');

        /*
         * SHACL constraint
         */
        $this->importTurtle(file_get_contents($this->examplesPath . 'example-hasValue.ttl'));
        $shapes = $this->shaclEngine->getNodeShapes();

        /*
         * instance data
         */
        $this->importTurtle('
            @prefix foaf: <'. $this->commonNamespaces->getUri('foaf') .'> .

            <http://female/1> a foaf:Person ;
                foaf:gender "female" .

            <http://female/2> a foaf:Person ;
                foaf:gender "female" .
        ');

        $this->assertEquals(0, count($this->fixture->handle($shapes[0])));
    }

    // Violation
    public function testHandleViolation()
    {
        $foaf = $this->commonNamespaces->getUri('foaf');
        $sh = $this->commonNamespaces->getUri('sh');

        /*
         * SHACL constraint
         */
        $this->importTurtle(file_get_contents($this->examplesPath . 'example-hasValue.ttl'));
        $shapes = $this->shaclEngine->getNodeShapes();

        /*
         * instance data
         */
        $this->importTurtle('
            @prefix foaf: <'. $this->commonNamespaces->getUri('foaf') .'> .

            <http://man/1> a foaf:Person ;
                foaf:gender "man" .
        ');

        $this->assertEquals(1, count($this->fixture->handle($shapes[0])));
    }
}
