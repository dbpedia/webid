<?php

namespace Shacl;

use Saft\Rdf\NamedNode;
use Saft\Rdf\Node;

/*
 * Represents a validation report.
 * FYI: https://www.w3.org/TR/shacl/#results-validation-result
 *
    example:

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
 *
 */

/**
 * Represents a sh:ValidationResult entry.
 *
 * @todo what if shape resulted in valid data? No ValidationResult or adapted severity?
 */
class ValidationResult
{
    protected $focusNode;
    protected $resultMessages;
    protected $resultPath;
    protected $resultSeverity;
    protected $sourceConstraintComponent;
    protected $sourceShape;
    protected $value;

    protected $supportedContraintComponents = array(
        'sh:AndConstraintComponent',
        'sh:HasValueConstraintComponent',
        'sh:MinMaxCountConstraintComponent',
        'sh:OrConstraintComponent',
    );

    /**
     * @param NamedNode $focusNode
     * @param NamedNode $resultPath
     * @param Severity $resultSeverity
     * @param NamedNode $sourceConstraintComponent
     * @param Node $sourceShape = null
     * @param Node $value = null
     * @param array $resultMessages = array()
     * @throws ShaclException if $sourceConstraintComponent is invalid
     */
    public function __construct(
        NamedNode $focusNode,
        NamedNode $resultPath,
        Severity $resultSeverity,
        NamedNode $sourceConstraintComponent,
        Node $sourceShape = null,
        array $resultMessages = array(),
        Node $value = null
    ) {
        // check sourceConstraintComponent
        $found = false;
        foreach ($this->supportedContraintComponents as $component) {
            if ($sourceConstraintComponent->getUri() == $component) {
                $found = true;
                break;
            }
        }
        if (false === $found) {
            throw new ShaclException(
                'Value of $sourceConstraintComponent must be one of '
                . implode (', ', $this->supportedContraintComponents)
                . '. Given was '. $sourceConstraintComponent->getUri()
            );
        }

        $this->sourceConstraintComponent = $sourceConstraintComponent;

        $this->focusNode = $focusNode;
        $this->resultMessages = $resultMessages;
        $this->resultPath = $resultPath;
        $this->resultSeverity = $resultSeverity;
        $this->sourceShape = $sourceShape;
        $this->value = $value;
    }

    /**
     * @return NamedNode Focus node
     */
    public function getFocusNode() : NamedNode
    {
        return $this->focusNode;
    }

    /**
     * @return array Array of string entries
     */
    public function getResultMessages() : array
    {
        return $this->resultMessages;
    }

    /**
     * @return NamedNode Result path
     */
    public function getResultPath() : NamedNode
    {
        return $this->resultPath;
    }

    /**
     * Checks if knowledge base conforms to given constraints.
     *
     * @return bool True, if knowledge base conforms to given constraints, false otherwise.
     */
    public function getResultSeverity() : Severity
    {
        return $this->resultSeverity;
    }

    /**
     * @return string
     * @todo: add available component IRIs
     */
    public function getSourceConstraintComponent() : string
    {
        return $this->sourceConstraintComponent;
    }

    /**
     *
     */
    public function getSourceShape() : Node
    {
        return $this->sourceShape;
    }

    /**
     * @return Node
     */
    public function getValue() : Node
    {
        return $this->value;
    }
}
