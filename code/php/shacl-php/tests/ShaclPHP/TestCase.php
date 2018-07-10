<?php

namespace Tests\ShaclPHP;

use Knorke\DataBlankHelper;
use Knorke\InMemoryStore;
use Knorke\Importer;
use Knorke\ParserFactory;
use Knorke\ResourceGuyHelper;
use PHPUnit\Framework\TestCase as PHPUnitTestCase;
use Saft\Rdf\CommonNamespaces;
use Saft\Rdf\NamedNode;
use Saft\Rdf\NodeFactoryImpl;
use Saft\Rdf\RdfHelpers;
use Saft\Rdf\Statement;
use Saft\Rdf\StatementFactoryImpl;
use Saft\Rdf\StatementIterator;
use Saft\Rdf\StatementIteratorFactoryImpl;
use Saft\Sparql\Query\QueryFactoryImpl;
use Saft\Sparql\Result\ResultFactoryImpl;
use Saft\Sparql\Result\SetResult;
use Saft\Store\Store;
use ShaclPHP\ConstraintComponentHandlerFactory;
use ShaclPHP\ShaclEngine;

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

    protected $commonNamespaces;
    protected $dataBlankHelper;
    protected $examplesPath;
    protected $importer;
    protected $nodeFactory;
    protected $parserFactory;
    protected $queryFactory;
    protected $rdfHelpers;
    protected $ResourceGuyHelper;
    protected $shaclEngine;
    protected $statementFactory;
    protected $statementIteratorFactory;
    protected $store;

    public function setUp()
    {
        global $dbConfig;

        parent::setUp();

        $this->commonNamespaces = new CommonNamespaces();
        $this->rdfHelpers = new RdfHelpers();
        $this->nodeFactory = new NodeFactoryImpl($this->rdfHelpers);
        $this->queryFactory = new QueryFactoryImpl($this->rdfHelpers);
        $this->statementFactory = new StatementFactoryImpl();
        $this->statementIteratorFactory = new StatementIteratorFactoryImpl();
        $this->parserFactory = new ParserFactory(
            $this->nodeFactory,
            $this->statementFactory,
            $this->statementIteratorFactory,
            $this->rdfHelpers
        );

        // setup our basic Store implementation, running fully in memory
        $this->store = new InMemoryStore(
            $this->commonNamespaces,
            $this->rdfHelpers,
            $this->nodeFactory
        );

        $this->resourceGuyHelper = new ResourceGuyHelper(
            $this->store,
            array(),
            $this->statementFactory,
            $this->nodeFactory,
            $this->rdfHelpers,
            $this->commonNamespaces
        );

        $this->importer = new Importer(
            $this->store,
            $this->parserFactory,
            $this->nodeFactory,
            $this->statementFactory,
            $this->rdfHelpers,
            $this->commonNamespaces
        );

        $this->constraintComponentHandlerFactory = new ConstraintComponentHandlerFactory(
            $this->nodeFactory,
            $this->commonNamespaces,
            $this->resourceGuyHelper
        );

        $this->shaclEngine = new ShaclEngine(
            $this->rdfHelpers,
            $this->commonNamespaces,
            $this->nodeFactory,
            $this->store,
            $this->resourceGuyHelper,
            $this->constraintComponentHandlerFactory
        );

        $this->examplesPath = __DIR__ .'/../examples/';
    }

    /**
     * This assertion consumes the StatementIterator and counts its entries until it is empty. It automatically
     * calls assertTrue and -False on $statementIterator->valid() from time to time.
     *
     * @param int $expectedCount
     * @param StatementIterator $statementIterator
     * @param string $message
     */
    public function assertCountStatementIterator(
        int $expectedCount,
        \Iterator $statementIterator,
        string $message = null
    ) {
        if (true == empty($message)) {
            $message = 'Assertion about count of statements. Expected: '. $expectedCount .', Actual: %s';
        }
        $i = 0;
        foreach ($statementIterator as $statement) {
            ++$i;
        }
        $this->assertEquals($i, $expectedCount, sprintf($message, $i));
    }

    /**
     * Checks two lists which implements \Iterator interface, if they contain the same Statement instances.
     * The checks will be executed using PHPUnit's assert functions.
     *
     * @param SetResult $expected
     * @param SetResult $actual
     */
    public function assertSetIteratorEquals(SetResult $expected, SetResult $actual)
    {
        $expectedEntries = array();
        foreach ($expected as $entry) {
            // serialize entry and hash it afterwards to use it as key for $entriesToCheck array.
            // later on we only check the other list that each entry, serialized and hashed, has
            // its equal key in the list.
            // the structure of each entry is an associative array which contains Node instances.
            $entryString = '';
            foreach ($entry as $key => $nodeInstance) {
                if ($nodeInstance->isConcrete()) {
                    // build a string of all entries of $entry and generate a hash based on that later on.
                    $entryString .= $nodeInstance->toNQuads() . ' ';
                } else {
                    throw new \Exception('Non-concrete Node instance in SetResult instance found.');
                }
            }
            $expectedEntries[$entryString] = $entry;
        }

        $actualEntries = array();
        foreach ($actual as $entry) {
            $entryString = '';
            foreach ($entry as $key => $nodeInstance) {
                if ($nodeInstance->isConcrete()) {
                    // build a string of all entries of $entry and generate a hash based on that later on.
                    $entryString .= $nodeInstance->toNQuads() . ' ';
                } else {
                    throw new \Exception('Non-concrete Node instance in SetResult instance found.');
                }
            }
            $actualEntries[$entryString] = $entry;
        }

        $notFoundEntries = array();
        foreach ($expectedEntries as $expectedEntry) {
            $foundExpectedEntry = false;

            // 1. generate a string which represents all nodes of an expected set entry
            $expectedEntryString = '';
            foreach ($expectedEntry as $nodeInstance) {
                $expectedEntryString .= $nodeInstance->toNQuads() . ' ';
            }

            // 2. for each actual entry check their generated string against the expected one
            foreach ($actualEntries as $actualEntry) {
                $actualEntryString = '';
                foreach ($actualEntry as $nodeInstance) {
                    $actualEntryString .= $nodeInstance->toNQuads() . ' ';
                }
                if ($actualEntryString == $expectedEntryString) {
                    $foundExpectedEntry = true;
                    break;
                }
            }

            if (false == $foundExpectedEntry) {
                $notFoundEntries[] = $expectedEntryString;
            }
        }

        // first simply check of the number of given actual entries and expected
        if (count($actualEntries) != count($expectedEntries)) {
            $this->fail('Expected '. count($expectedEntries) . ' entries, but got '. count($actualEntries));
        }

        if (!empty($notFoundEntries)) {
            echo PHP_EOL . PHP_EOL . 'Given entries, but not found:' . PHP_EOL;
            var_dump($notFoundEntries);

            echo PHP_EOL . PHP_EOL . 'Actual entries:' . PHP_EOL;
            foreach ($actualEntries as $entries) {
                echo '- ';
                foreach ($entries as $entry) {
                    echo $entry->toNQuads() .' ';
                }
                echo PHP_EOL;
                echo PHP_EOL;
            }

            $this->fail(count($notFoundEntries) .' entries where not found.');

        // check variables in the end
        } elseif (0 == count($notFoundEntries)) {
            $this->assertEquals($expected->getVariables(), $actual->getVariables());
        }
    }

    /**
     * Checks two lists which implements \Iterator interface, if they contain the same elements.
     * The checks will be executed using PHPUnit's assert functions.
     *
     * @param StatementIterator $expected
     * @param StatementIterator $actual
     * @param boolean $debug optional, default: false
     * @todo implement a more precise way to check blank nodes (currently we just count expected
     *       and actual numbers of statements with blank nodes)
     */
    public function assertStatementIteratorEquals(
        StatementIterator $expected,
        StatementIterator $actual,
        $debug = false
    ) {
        $entriesToCheck = array();
        $expectedStatementsWithBlankNodeCount = 0;

        foreach ($expected as $statement) {
            // serialize entry and hash it afterwards to use it as key for $entriesToCheck array.
            // later on we only check the other list that each entry, serialized and hashed, has
            // its equal key in the list.
            if (!$statement->isConcrete()) {
                $this->markTestIncomplete('Comparison of variable statements in iterators not yet implemented.');
            }
            if ($this->statementContainsNoBlankNodes($statement)) {
                $entriesToCheck[hash('sha256', $statement->toNQuads())] = false;
            } else {
                ++$expectedStatementsWithBlankNodeCount;
            }
        }

        // contains a list of all entries, which were not found in $expected.
        $actualEntriesNotFound = array();
        $notCheckedEntries = array();
        $foundEntries = array();
        $actualStatementsWithBlankNodeCount = 0;

        foreach ($actual as $statement) {
            if (!$statement->isConcrete()) {
                $this->markTestIncomplete("Comparison of variable statements in iterators not yet implemented.");
            }
            $statmentHash = hash('sha256', $statement->toNQuads());
            // statements without blank nodes
            if (isset($entriesToCheck[$statmentHash]) && $this->statementContainsNoBlankNodes($statement)) {
                // if entry was found, mark it.
                $entriesToCheck[$statmentHash] = true;
                $foundEntries[] = $statement;

            // handle statements with blank nodes separate because blanknode ID is random
            // and therefore gets lost when stored (usually)
            } elseif (false == $this->statementContainsNoBlankNodes($statement)) {
                ++$actualStatementsWithBlankNodeCount;

            // statement was not found
            } else {
                $actualEntriesNotFound[] = $statement;
                $notCheckedEntries[] = $statement;
            }
        }

        if (!empty($actualEntriesNotFound) || !empty($notCheckedEntries)) {
            $message = 'The StatementIterators are not equal.';
            if (!empty($actualEntriesNotFound)) {
                if ($debug) {
                    echo PHP_EOL . 'Following statements where not expected, but found: ';
                    var_dump($actualEntriesNotFound);
                }
                $message .= ' ' . count($actualEntriesNotFound) . ' Statements where not expected.';
            }
            if (!empty($notCheckedEntries)) {
                if ($debug) {
                    echo PHP_EOL . 'Following statements where not present, but expected: ';
                    var_dump($notCheckedEntries);
                }
                $message .= ' ' . count($notCheckedEntries) . ' Statements where not present but expected.';
            }
            $this->assertFalse(!empty($actualEntriesNotFound) || !empty($notCheckedEntries), $message);

        // compare count of statements with blank nodes
        } elseif ($expectedStatementsWithBlankNodeCount != $actualStatementsWithBlankNodeCount) {
            $this->assertFalse(
                true,
                'Some statements with blank nodes where not found. '
                    . 'Expected: ' . $expectedStatementsWithBlankNodeCount
                    . 'Actual: ' . $actualStatementsWithBlankNodeCount
            );

        } else {
            $this->assertTrue(true);
        }
    }

    /**
     * Imports RDF into in memory store.
     *
     * @param string $rdf RDF in Turtle format.
     */
    protected function importTurtle(string $rdf)
    {
        $importer = new Importer(
            $this->store,
            $this->parserFactory,
            $this->nodeFactory,
            $this->statementFactory,
            $this->rdfHelpers,
            $this->commonNamespaces
        );

        $importer->importString(
            $rdf,
            $this->nodeFactory->createNamedNode('http://to-be-ignored/'),
            'turtle'
        );
    }

    /**
     * @param Statement $statement
     * @return bool
     */
    protected function statementContainsNoBlankNodes(Statement $statement) : bool
    {
        return false == $statement->getSubject()->isBlank()
            && false == $statement->getObject()->isBlank();
    }
}
