<?php

namespace ShaclPHP;

use Knorke\ResourceGuyHelper;
use Saft\Rdf\CommonNamespaces;
use Saft\Rdf\NodeFactory;
use Saft\Rdf\RdfHelpers;
use Saft\Store\Store;
use Shacl\ValidationReport;
use ShaclPHP\ConstraintComponentHandler\MinCountConstraintComponentHandler;

class ShaclEngine
{
    protected $commonNamespaces;
    protected $constraintComponentHandlerFactory;
    protected $nodeFactory;
    protected $rdfHelpers;
    protected $resourceGuyHelper;
    protected $shapeHandler;
    protected $store;

    /**
     * @param RdfHelpers $rdfHelpers
     * @param CommonNamespaces $commonNamespaces
     * @param NodeFactory $nodeFactory
     * @param Store $store
     * @param ResourceGuyHelper $resourceGuyHelper
     * @param ConstraintComponentHandlerFactory $constraintComponentHandlerFactory
     */
    public function __construct(
        RdfHelpers $rdfHelpers,
        CommonNamespaces $commonNamespaces,
        NodeFactory $nodeFactory,
        Store $store,
        ResourceGuyHelper $resourceGuyHelper,
        ConstraintComponentHandlerFactory $constraintComponentHandlerFactory
    ) {
        $this->commonNamespaces = $commonNamespaces;
        $this->constraintComponentHandlerFactory = $constraintComponentHandlerFactory;
        $this->nodeFactory = $nodeFactory;
        $this->rdfHelpers = $rdfHelpers;
        $this->resourceGuyHelper = $resourceGuyHelper;
        $this->store = $store;
    }

    /**
     * Checks content of in memory store, if its SHACL shapes apply to the given instance data.
     *
     * @return ValidationReport
     */
    public function check() : ValidationReport
    {
        // load available SHACL shapes
        $shapes = $this->getNodeShapes();
        $matchingShapes = array();

        // find shapes which match classes of the instance data
        foreach ($shapes as $shape) {
            $instances = $this->store->query('
                PREFIX rdf: <'. $this->commonNamespaces->getUri('rdf') .'>
                SELECT * WHERE {
                    ?instance rdf:type <'. $shape['sh:targetClass']['_idUri']->getUri() .'> .
                }
            ');

            if (0 < count($instances)) {
                $matchingShapes[] = $shape;
            }
        }

        /*
         * check if rules apply
         *
         * for each shape the whole graph will be checked. each constraint has its according handler,
         * e.g. sh:minCount has MinCountConstraintComponentHandler.
         */
        $results = array();
        foreach ($matchingShapes as $shape) {
            $handler = null;

            // sh:and
            if (isset($shape['sh:and'])) {
                $handler = $this->constraintComponentHandlerFactory->create('AndConstraintComponentHandler');

            // sh:minCount or sh:maxCount
            } elseif (isset($shape['sh:property']['sh:minCount']) || isset($shape['sh:property']['sh:maxCount'])) {
                $handler = $this->constraintComponentHandlerFactory->create('MinMaxCountConstraintComponentHandler');

            // sh:hasValue
            } elseif (isset($shape['sh:property']['sh:hasValue'])) {
                $handler = $this->constraintComponentHandlerFactory->create('HasValueConstraintComponentHandler');

            // sh:or
            } elseif (isset($shape['sh:or'])) {
                $handler = $this->constraintComponentHandlerFactory->create('OrConstraintComponentHandler');
            }

            if (null !== $handler) {
                $validationResults = $handler->handle($shape);
                $results = array_merge($validationResults, $results);
            }
        }

        // generate report and return it
        return new ValidationReport($results);
    }

    /**
     * @return array Array of Shape instances
     * @todo distingish between sh:NodeShape and sh:PropertyShape
     */
    public function getNodeShapes() : array
    {
        // get shape data as ResourceGuy instances
        $shapeGuys = $this->resourceGuyHelper->getInstancesByType($this->commonNamespaces->getUri('sh') . 'NodeShape', 2);

        // transform ResourceGuy instances to Shape instances
        $foaf = $this->commonNamespaces->getUri('foaf');
        $rdf = $this->commonNamespaces->getUri('rdf');
        $sh = $this->commonNamespaces->getUri('sh');

        $shapes = array();
        foreach ($shapeGuys as $guy) {
            /*
             * if $guy has a sh:and or sh:or relation, load referenced entries
             */
            if (isset($guy['sh:or']) || isset($guy['sh:and'])) {
                if (isset($guy['sh:and'])) {
                    $property = $sh . 'and';
                    $shortedProperty = 'sh:and';
                } else {
                    $property = $sh . 'or';
                    $shortedProperty = 'sh:or';
                }

                $result = $this->store->query('SELECT * WHERE {
                    <'. $guy['_idUri'] .'> <'. $property .'> ?entry .
                    ?entry ?p ?o.
                }');

                $subs = array();
                foreach ($result as $entry) {
                    $entryId = $entry['entry']->getBlankId();
                    if (false === isset($subs[$entry['entry']->getBlankId()])) {
                        $subs[$entryId] = array();
                    }
                    $subs[$entryId][$entry['p']->getUri()] = $this->rdfHelpers->getValueForNode($entry['o']);
                }

                $subGuys = array();
                foreach ($subs as $blankId => $array) {
                    $subGuys[] = $this->resourceGuyHelper->createInstanceByArray($array);
                }

                $guy[$shortedProperty] = $subGuys;
            }

            $shapes[] = $guy;
        }

        return $shapes;
    }
}
