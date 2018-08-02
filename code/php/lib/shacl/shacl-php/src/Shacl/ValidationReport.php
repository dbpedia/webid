<?php

namespace Shacl;

/*
 * Represents a validation report.
 * FYI: https://www.w3.org/TR/shacl/#validation-report
 *
    [
 *      a sh:ValidationReport ;                 <===
        sh:conforms false ;
        sh:result
        [   a sh:ValidationResult ;
            sh:resultSeverity sh:Warning ;
            sh:focusNode ex:MyInstance ;
            sh:resultPath ex:myProperty ;
            sh:value "http://toomanycharacters"^^xsd:anyURI ;
            sh:sourceConstraintComponent sh:DatatypeConstraintComponent ;
            sh:sourceShape _:b1 ;
        ] ,
        [   a sh:ValidationResult ;
            sh:resultSeverity sh:Violation ;
            sh:focusNode ex:MyInstance ;
            sh:resultPath ex:myProperty ;
            sh:value "http://toomanycharacters"^^xsd:anyURI ;
            sh:resultMessage "Too many characters"@en ;
            sh:resultMessage "Zu viele Zeichen"@de ;
            sh:sourceConstraintComponent sh:MaxLengthConstraintComponent ;
            sh:sourceShape _:b2 ;
        ]
    ] .
 *
 */
class ValidationReport
{
    protected $results;

    /**
     * @param array $results Optional, default is array()
     */
    public function __construct(array $results = array())
    {
        $this->results = $results;
    }

    /**
     * Checks if knowledge base conforms to given constraints.
     *
     * @return bool True, if knowledge base conforms to given constraints, false otherwise.
     */
    public function conforms() : bool
    {
        return false === $this->hasConstraintViolation();
    }

    /**
     * @return bool True if there is one ValidationResult instance with serverity equal to sh:Info, false otherwise.
     */
    public function hasConstraintInfo() : bool
    {
        foreach ($this->results as $result) {
            if ('sh:Info' == $result->getResultSeverity()->getType()) {
                return true;
            }
        }

        return false;
    }

    /**
     * @return bool True if there is one ValidationResult instance with serverity equal to sh:Violation, false otherwise.
     */
    public function hasConstraintViolation() : bool
    {
        foreach ($this->results as $result) {
            if ('sh:Violation' == $result->getResultSeverity()->getType()) {
                return true;
            }
        }

        return false;
    }

    /**
     * @return bool True if there is one ValidationResult instance with serverity equal to sh:Warning, false otherwise.
     */
    public function hasConstraintWarning() : bool
    {
        foreach ($this->results as $result) {
            if ('sh:Warning' == $result->getResultSeverity()->getType()) {
                return true;
            }
        }

        return false;
    }

    /**
     * Returns results.
     *
     * @return array Array of ValidationResult instances
     */
    public function getResults() : array
    {
        return $this->results;
    }
}
