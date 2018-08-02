<?php

namespace Tests\Shacl;

use Shacl\Severity;

class SeverityTest extends TestCase
{
    public function setUp()
    {
        parent::setUp();
    }

    /*
     * Tests for instantiation
     */

    public function testInstantiation()
    {
        $this->fixture = new Severity('sh:Info');
        $this->fixture = new Severity('sh:Warning');
        $this->fixture = new Severity('sh:Violation');

        // we expect the test to run until here, without throwing an exception
        $this->assertEquals(true, true);
    }

    public function testInstantiationInvalidType()
    {
        $this->expectException('\Shacl\ShaclException');

        $this->fixture = new Severity('invalid');
    }
}
