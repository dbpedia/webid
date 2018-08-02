<?php

namespace Tests\Shacl;

use PHPUnit\Framework\TestCase as PHPUnitTestCase;
use Saft\Rdf\NodeFactoryImpl;
use Saft\Rdf\RdfHelpers;

/**
 * @codeCoverageIgnore
 */
class TestCase extends PHPUnitTestCase
{
    /**
     * Contains an instance of the class to test.
     *
     * @var mixed
     */
    protected $fixture;

    protected $nodeFactory;
    protected $rdfHelpers;

    public function setUp()
    {
        parent::setUp();

        $this->rdfHelpers = new RdfHelpers();
        $this->nodeFactory = new NodeFactoryImpl($this->rdfHelpers);
    }
}
