<?php

namespace Shacl;

/*
 * Represents a severity.
 * FYI: https://www.w3.org/TR/shacl/#severity
 *
    [
        a sh:ValidationReport ;
        sh:conforms false ;
        sh:result
        [   a sh:ValidationResult ;
            sh:resultSeverity sh:Warning ;                  <===
            sh:focusNode ex:MyInstance ;
            sh:resultPath ex:myProperty ;
            sh:value "http://toomanycharacters"^^xsd:anyURI ;
            sh:sourceConstraintComponent sh:DatatypeConstraintComponent ;
            sh:sourceShape _:b1 ;
        ]
    ] .
 *
 */

/**
 * Represents a sh:Severity entry (sh:Info, sh:Warning or sh:Violation).
 */
class Severity
{
    protected $type;

    /**
     * @param string $type Type of the Severity (sh:Info, sh:Warning or sh:Violation).
     */
    public function __construct(string $type)
    {
        if (false === in_array($type, array('sh:Info', 'sh:Warning', 'sh:Violation'))) {
            throw new ShaclException('Severity can only have type sh:Info, sh:Warning or sh:Violation.');
        }

        $this->type = $type;
    }

    /**
     * Returns type of the severity.
     *
     * @return string Either sh:Info, sh:Warning or sh:Violation.
     */
    public function getType() : string
    {
        return $this->type;
    }
}
