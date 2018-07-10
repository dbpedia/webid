<?php

namespace ShaclPHP\ConstraintComponentHandler;

use Knorke\ResourceGuy;
use Knorke\ResourceGuyHelper;
use Saft\Rdf\Node;
use Saft\Rdf\NodeFactory;
use Shacl\Severity;
use Shacl\ValidationResult;
use ShaclPHP\ConstraintComponentHandler;

/**
 * Handles sh:MinCountConstraintComponent and sh:MaxCountConstraintComponentHandler
 *
 * sh:minCount specifies the minimum number of value nodes that satisfy the condition.
 * If the minimum cardinality value is 0 then this constraint is always satisfied and so may be omitted.
 *
 * sh:maxCount specifies the maximum number of value nodes that satisfy the condition.
 *
 * Example:
 *
 *  []
 *      rdf:type sh:PropertyShape ;
 *      sh:path foaf:firstName ;
 *      sh:minCount 1 ;                      <======
 *      sh:maxCount 1 .                      <======
 */
class MinMaxCountConstraintComponentHandler extends ConstraintComponentHandler
{
    /**
     * @param ResourceGuy $shape
     * @return array Array of ValidationResult instances
     */
    public function handle(ResourceGuy $shape) : array
    {
        // get all instances of the current sh:targetClass
        if (isset($shape['sh:targetClass']['_idUri'])) {
            $relatedEntries = $this->resourceGuyHelper->getInstancesByType($shape['sh:targetClass']['_idUri']->getUri());
        } else {
            $relatedEntries = $this->resourceGuyHelper->getInstancesByType($shape['sh:targetClass']->getUri());
        }

        // sh:minCount
        $results = $this->handleMinCount($shape, $relatedEntries);

        // sh:maxCount
        $results = array_merge($results, $this->handleMaxCount($shape, $relatedEntries));

        return $results;
    }

    /**
     * @param ResourceGuy $shape
     * @param array $relatedEntries
     * @return array Array of ValidationResult instances
     */
    protected function handleMaxCount(ResourceGuy $shape, array $relatedEntries) : array
    {
        // maximum count of the property
        if (isset($shape['sh:property']['sh:maxCount'])) {
            $maxCount = $shape['sh:property']['sh:maxCount']->getValue();
            $shPath = $shape['sh:property']['sh:path'];
            $shPathUri = $shape['sh:property']['sh:path']->getUri(); // = property

        } elseif (isset($shape['sh:maxCount'])) {
            $maxCount = $shape['sh:maxCount']->getValue();
            $shPath = $shape['sh:path'];
            $shPathUri = $shape['sh:path']->getUri(); // = property

        } else {
            return array();
        }

        $results = array();

        // check that any instance match given constraint
        foreach ($relatedEntries as $entry) {
            $isOk = false;
            if (0 < $maxCount) {
                if ($entry[$shPathUri] instanceof Node) {
                    // OK
                    $isOk = true;
                } elseif (is_array($entry[$shPathUri]) && $maxCount >= count($entry[$shPathUri])) {
                    // OK
                    $isOk = true;
                } elseif (false == isset($entry[$shPathUri])) {
                    // OK
                    $isOk = true;
                } else {
                    // violation
                }
            }
            if (false === $isOk) {
                $results[] = new ValidationResult(
                    $entry['_idUri'],                                                           // focusNode
                    $shPath,                                                                    // resultPath
                    new Severity('sh:Violation'),                                               // resultSeverity
                    $this->nodeFactory->createNamedNode('sh:MinMaxCountConstraintComponent'),   // sourceConstraintComponent
                    $shape['_idUri'],                                                           // sourceShape
                    array(
                        $entry['_idUri']->getUri() .' has to have property '. $shPathUri .' maximum '. $maxCount .' time(s).'
                        . ' It has '. count($entry[$shPathUri]))
                );
            }
        }
        return $results;
    }

    /**
     * @param ResourceGuy $shape
     * @param array $relatedEntries
     * @return array Array of ValidationResult instances
     */
    protected function handleMinCount(ResourceGuy $shape, array $relatedEntries) : array
    {
        // minimum count of the property
        if (isset($shape['sh:property']['sh:minCount'])) {
            $minCount = $shape['sh:property']['sh:minCount']->getValue();
            $shPath = $shape['sh:property']['sh:path'];
            $shPathUri = $shape['sh:property']['sh:path']->getUri(); // = property

        } elseif (isset($shape['sh:minCount'])) {
            $minCount = $shape['sh:minCount']->getValue();
            $shPath = $shape['sh:path'];
            $shPathUri = $shape['sh:path']->getUri(); // = property

        } else {
            return array();
        }

        $results = array();

        // check that any instance match given constraint
        foreach ($relatedEntries as $entry) {
            $isOk = false;

            if (0 <= $minCount) {
                if ($entry[$shPathUri] instanceof Node) {
                    // OK
                    $isOk = true;
                } elseif (is_array($entry[$shPathUri]) && $minCount < count($entry[$shPathUri])) {
                    // OK
                    $isOk = true;
                } else {
                    // violation
                }
            }

            if (false === $isOk) {
                $results[] = new ValidationResult(
                    $entry['_idUri'],                                                           // focusNode
                    $shPath,                                                                    // resultPath
                    new Severity('sh:Violation'),                                               // resultSeverity
                    $this->nodeFactory->createNamedNode('sh:MinMaxCountConstraintComponent'),   // sourceConstraintComponent
                    isset($shape['_idUri']) ? $shape['_idUri'] : null,                          // sourceShape
                    array($entry['_idUri']->getUri() .' has to have property '. $shPathUri .' at least '. $minCount .' time(s).')
                );
            }
        }

        return $results;
    }
}
