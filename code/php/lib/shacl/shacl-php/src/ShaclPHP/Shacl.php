<?php

namespace ShaclPHP;

use Knorke\Importer;
use Knorke\InMemoryStore;
use Knorke\ResourceGuyHelper;
use Knorke\ParserFactory;
use Saft\Rdf\CommonNamespaces;
use Saft\Rdf\NodeFactoryImpl;
use Saft\Rdf\RdfHelpers;
use Saft\Rdf\StatementFactoryImpl;
use Saft\Rdf\StatementIteratorFactoryImpl;
use Saft\Store\Store;
use Shacl\ValidationReport;
use ShaclPHP\ConstraintComponentHandler\MinCountConstraintComponentHandler;

class Shacl
{
    protected $shaclEngine;
    protected $store;

    /**
     * Helper class which initiates all dependencies and provides a ready to use SHACL engine.
     */
    public function __construct()
    {
        $commonNamespaces = new CommonNamespaces();
        $rdfHelpers = new RdfHelpers();
        $this->nodeFactory = new NodeFactoryImpl($rdfHelpers);
        $statementFactory = new StatementFactoryImpl($rdfHelpers);
        $this->store = new InMemoryStore($commonNamespaces, $rdfHelpers, $this->nodeFactory);
        $resourceGuyHelper = new ResourceGuyHelper(
            $this->store,
            array(),
            $statementFactory,
            $this->nodeFactory,
            $rdfHelpers,
            $commonNamespaces
        );
        $constraintComponentHandlerFactory = new ConstraintComponentHandlerFactory(
            $this->nodeFactory,
            $commonNamespaces,
            $resourceGuyHelper
        );
        $statementIteratorFactory = new StatementIteratorFactoryImpl();
        $parserFactory = new ParserFactory(
            $this->nodeFactory,
            $statementFactory,
            $statementIteratorFactory,
            $rdfHelpers
        );
        $this->importer = new Importer(
            $this->store,
            $parserFactory,
            $this->nodeFactory,
            $statementFactory,
            $rdfHelpers,
            $commonNamespaces
        );

        $this->shaclEngine = new ShaclEngine(
            $rdfHelpers,
            $commonNamespaces,
            $this->nodeFactory,
            $this->store,
            $resourceGuyHelper,
            $constraintComponentHandlerFactory
        );
    }

    /**
     * Checks content of in memory store, if its SHACL shapes apply to the given instance data.
     *
     * @return ValidationReport
     */
    public function check() : ValidationReport
    {
        return $this->shaclEngine->check();
    }

    /**
     * @param string $rdf
     */
    public function importTurtleRdf(string $rdf)
    {
        $this->importer->importString(
            $rdf,
            $this->nodeFactory->createNamedNode('http://to-be-ignored'),
            'turtle'
        );
    }
}
