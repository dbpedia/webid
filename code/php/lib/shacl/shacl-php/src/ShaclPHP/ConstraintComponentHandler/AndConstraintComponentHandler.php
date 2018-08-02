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
 * sh:AndConstraintComponent
 *
 * sh:and specifies the condition that each value node conforms to all provided shapes.
 * This is comparable to conjunction and the logical "and" operator.
 *
 * Example:
 *
 *  ex:OrConstraintExampleShape
 *    a sh:NodeShape ;
 *    sh:targetNode ex:Bob ;
 *    sh:and [
 *          sh:path ex:firstName ;
 *          sh:minCount 1 ;
 *      ] ,
 *      [
 *          sh:path ex:lastName ;
 *          sh:minCount 1 ;
 *      ].
 */
class AndConstraintComponentHandler extends ConstraintComponentHandler
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

        // assuming array of Node instances
        if (isset($shape['sh:and']) && true === is_array($shape['sh:and'])) {
            // check each one, if all conform empty array, otherwise appropriate ValidationResult instances
            foreach ($shape['sh:and'] as $entry) {
                /*
                 * loads related ConstraintComponentHandler and receives result
                 * all constraints must be met, then the shape results in a success
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
                $constraintCompHandlerName = $this->constraintComponentHandlerFactory->determineConstraintName($entry);

                // if known constraint component handler could be determined
                if (null !== $constraintCompHandlerName) {
                    $constraintComponentHandler = $this->constraintComponentHandlerFactory->create(
                        $constraintCompHandlerName
                    );
                    $entry['sh:targetClass'] = $shape['sh:targetClass'];
                    $result = $constraintComponentHandler->handle($entry);

                    if (0 == count($result)) {
                        // OK
                    } else {
                        // something went bad
                        $results = array_merge($results, $result);
                    }

                } else {
                    // TODO how to handle unknown constraint component handler?
                    throw new ShaclException('Unknown constraint component handler given: '. implode(', ', $entry->getArrayCopy()));
                }
            }
        }

        // if $results is not empty, something went wrong
        return $results;
    }
}
