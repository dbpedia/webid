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
 * sh:OrConstraintComponent
 *
 * sh:or specifies the condition that each value node conforms to at least one of the provided shapes.
 * This is comparable to disjunction and the logical "or" operator.
 *
 * Example:
 *
 *  ex:OrConstraintExampleShape
 *    a sh:NodeShape ;
 *    sh:targetNode ex:Bob ;
 *    sh:or [
 *          sh:path ex:firstName ;
 *          sh:minCount 1 ;
 *      ] ,
 *      [
 *          sh:path ex:givenName ;
 *          sh:minCount 1 ;
 *      ].
 */
class OrConstraintComponentHandler extends ConstraintComponentHandler
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
        $conforms = false;

        // assuming array of Node instances
        if (isset($shape['sh:or']) && true === is_array($shape['sh:or'])) {
            // check each one and stop, if at least one returns a non-violation
            foreach ($shape['sh:or'] as $orEntry) {
                /*
                 * loads related ConstraintComponentHandler and receives result
                 * if at least one is a non-violation OR one has no ValidationResult,
                 * the shape results in a success
                 *
                 *
                 * example:

                     [
                         sh:path foaf:firstName ;
                         sh:minCount 1 ;
                     ],
                     [
                         sh:path foaf:givenName ;
                         sh:minCount 1 ;
                     ] .
                 */
                $constraintCompHandlerName = $this->constraintComponentHandlerFactory->determineConstraintName($orEntry);

                // if known constraint component handler could be determined
                if (null !== $constraintCompHandlerName) {
                    $constraintComponentHandler = $this->constraintComponentHandlerFactory->create(
                        $constraintCompHandlerName
                    );
                    $orEntry['sh:targetClass'] = $shape['sh:targetClass'];
                    $result = $constraintComponentHandler->handle($orEntry);

                    // OK
                    if (0 == count($result)) {
                        $conforms = true;
                        break;

                    // something went bad
                    } else {
                        $results = array_merge($results, $result);
                    }

                } else {
                    // TODO how to handle unknown constraint component handler?
                    throw new ShaclException('Unknown constraint component handler given: '. implode(', ', $orEntry->getArrayCopy()));
                }
            }
        }

        // OK (at least one constraint works)
        if ($conforms) {
            return array(); // TODO empty array okay here?

        // violation (none of the constraints worked)
        } else {
            return $results;
        }
    }
}
