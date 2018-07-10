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
 * sh:HasValueConstraintComponent
 *
 * sh:hasValue specifies the condition that at least one value node is equal to the given RDF term.
 *
 * Example:
 *
 *  :WomanPropertyShape
 *      rdf:type sh:PropertyShape ;
 *      sh:path foaf:gender ;
 *      sh:hasValue "female" .              <========
 */
class HasValueConstraintComponentHandler extends ConstraintComponentHandler
{
    /**
     * @param ResourceGuy $shape
     * @return array Array of ValidationResult instances
     */
    public function handle(ResourceGuy $shape) : array
    {
        // get all instances of the current sh:targetClass
        $relatedEntries = $this->resourceGuyHelper->getInstancesByType($shape['sh:targetClass']['_idUri']->getUri());
        $results = array();

        // check that any instance match given constraint
        foreach ($relatedEntries as $entry) {
            $shPath = $shape['sh:property']['sh:path'];
            $shPathUri = $shape['sh:property']['sh:path']->getUri(); // = property
            $requiredValue = $shape['sh:property']['sh:hasValue']->getValue(); // required value
            $isOk = false;

            if ($entry[$shPathUri]->isLiteral()) {
                if ($requiredValue == $entry[$shPathUri]->getValue()) {
                    // OK
                    $isOk = true;
                } else {
                    // violation, values dont match
                }
            } else {
                // violation, needs to be literal
            }

            if (false === $isOk) {
                $results[] = new ValidationResult(
                    $entry['_idUri'],                                                       // focusNode
                    $shPath,                                                                // resultPath
                    new Severity('sh:Violation'),                                           // resultSeverity
                    $this->nodeFactory->createNamedNode('sh:HasValueConstraintComponent'),  // sourceConstraintComponent
                    $shape['_idUri'],                                                       // sourceShape
                    array($entry['_idUri']->getUri() .'\'s property '. $shPathUri .' must have as value: '. $requiredValue .'.')
                );
            }
        }

        return $results;
    }
}
