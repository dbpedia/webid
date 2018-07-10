<?php

namespace Tests\ShaclPHP\ConstraintComponentHandler;

use ShaclPHP\ConstraintComponentHandler\OrConstraintComponentHandler;
use Tests\ShaclPHP\TestCase;

class OrConstraintComponentHandlerTest extends TestCase
{
    public function setUp()
    {
        parent::setUp();

        $this->fixture = new OrConstraintComponentHandler(
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
        $this->importTurtle(file_get_contents($this->examplesPath . 'example-or.ttl'));
        $shapes = $this->shaclEngine->getNodeShapes();

        /*
         * instance data
         */
        $this->importTurtle('
            @prefix foaf: <'. $this->commonNamespaces->getUri('foaf') .'> .

            <http://female/1> a foaf:Person ;
                foaf:firstName "Foo" .
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
        $this->importTurtle(file_get_contents($this->examplesPath . 'example-or.ttl'));
        $shapes = $this->shaclEngine->getNodeShapes();

        /*
         * instance data
         */
        $this->importTurtle('
            @prefix foaf: <'. $this->commonNamespaces->getUri('foaf') .'> .

            <http://female/1> a foaf:Person ;
                foaf:givenName "1" ,
                               "too much" .
        ');

        $this->assertEquals(2, count($this->fixture->handle($shapes[0])));
    }
}
