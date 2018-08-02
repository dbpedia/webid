<?php

namespace Tests\ShaclPHP\ConstraintComponentHandler;

use ShaclPHP\ConstraintComponentHandler\AndConstraintComponentHandler;
use Tests\ShaclPHP\TestCase;

class AndConstraintComponentHandlerTest extends TestCase
{
    public function setUp()
    {
        parent::setUp();

        $this->fixture = new AndConstraintComponentHandler(
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
        $this->importTurtle(file_get_contents($this->examplesPath . 'example-and.ttl'));
        $shapes = $this->shaclEngine->getNodeShapes();

        /*
         * instance data
         */
        $this->importTurtle('
            @prefix foaf: <'. $this->commonNamespaces->getUri('foaf') .'> .

            <http://female/1> a foaf:Person ;
                foaf:firstName "Foo" ;
                foaf:lastName "Bar" .
        ');

        $this->assertEquals(0, count($this->fixture->handle($shapes[0])));
    }

    // violation
    public function testHandleViolation()
    {
        $foaf = $this->commonNamespaces->getUri('foaf');
        $sh = $this->commonNamespaces->getUri('sh');

        /*
         * SHACL constraint
         */
        $this->importTurtle(file_get_contents($this->examplesPath . 'example-and.ttl'));
        $shapes = $this->shaclEngine->getNodeShapes();

        /*
         * instance data
         */
        $this->importTurtle('
            @prefix foaf: <'. $this->commonNamespaces->getUri('foaf') .'> .

            <http://female/1> a foaf:Person ;
                foaf:givenName "Bar" .
        ');

        $this->assertEquals(1, count($this->fixture->handle($shapes[0])));
    }
}
