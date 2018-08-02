<?php

namespace Tests\ShaclPHP;

use ShaclPHP\ConstraintComponentHandler;
use ShaclPHP\ConstraintComponentHandlerFactory;

class ConstraintComponentHandlerFactoryTest extends TestCase
{
    public function setUp()
    {
        parent::setUp();

        $this->fixture = new ConstraintComponentHandlerFactory(
            $this->nodeFactory,
            $this->commonNamespaces,
            $this->resourceGuyHelper
        );
    }

    /*
     * Tests for create
     */

    public function testCreate()
    {
        // handler found
        $this->assertTrue($this->fixture->create('MinMaxCountConstraintComponentHandler') instanceof ConstraintComponentHandler);

        // handler not available
        $this->assertNull($this->fixture->create('not-available-handler'));
    }
}
