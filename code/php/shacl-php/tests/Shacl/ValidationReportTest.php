<?php

namespace Tests\Shacl;

use Shacl\Severity;
use Shacl\ValidationReport;
use Shacl\ValidationResult;

class ValidationReportTest extends TestCase
{
    public function setUp()
    {
        parent::setUp();
    }

    /*
     * Tests for instantiation
     */

    public function testInstantiation()
    {
        $this->fixture = new ValidationReport(
            array( // results
                new ValidationResult(
                    $this->nodeFactory->createNamedNode('http://focusNode/'),
                    $this->nodeFactory->createNamedNode('http://resultPath/'),
                    new Severity('sh:Warning'),
                    $this->nodeFactory->createNamedNode('sh:MinMaxCountConstraintComponent'),
                    $this->nodeFactory->createNamedNode('http://value')
                )
            )
        );

        $this->assertTrue($this->fixture->conforms());
    }

    /*
     * Tests for conforms
     */

    public function testConforms()
    {
        // true
        $this->fixture = new ValidationReport(
            array( // results
                new ValidationResult(
                    $this->nodeFactory->createNamedNode('http://focusNode/'),
                    $this->nodeFactory->createNamedNode('http://resultPath/'),
                    new Severity('sh:Info'),
                    $this->nodeFactory->createNamedNode('sh:MinMaxCountConstraintComponent'),
                    $this->nodeFactory->createNamedNode('http://value')
                )
            )
        );
        $this->assertTrue($this->fixture->conforms());

        // true
        $this->fixture = new ValidationReport(
            array( // results
                new ValidationResult(
                    $this->nodeFactory->createNamedNode('http://focusNode/'),
                    $this->nodeFactory->createNamedNode('http://resultPath/'),
                    new Severity('sh:Warning'),
                    $this->nodeFactory->createNamedNode('sh:MinMaxCountConstraintComponent'),
                    $this->nodeFactory->createNamedNode('http://value')
                )
            )
        );
        $this->assertTrue($this->fixture->conforms());

        // false
        $this->fixture = new ValidationReport(
            array( // results
                new ValidationResult(
                    $this->nodeFactory->createNamedNode('http://focusNode/'),
                    $this->nodeFactory->createNamedNode('http://resultPath/'),
                    new Severity('sh:Violation'),
                    $this->nodeFactory->createNamedNode('sh:MinMaxCountConstraintComponent'),
                    $this->nodeFactory->createNamedNode('http://value')
                )
            )
        );
        $this->assertFalse($this->fixture->conforms());
    }

    /*
     * Tests for hasConstraintInfo
     */

    public function testHasConstraintInfo()
    {
        // false
        $this->fixture = new ValidationReport(
            array( // results
                new ValidationResult(
                    $this->nodeFactory->createNamedNode('http://focusNode/'),
                    $this->nodeFactory->createNamedNode('http://resultPath/'),
                    new Severity('sh:Info'),
                    $this->nodeFactory->createNamedNode('sh:MinMaxCountConstraintComponent'),
                    $this->nodeFactory->createNamedNode('http://value')
                )
            )
        );

        $this->assertTrue($this->fixture->hasConstraintInfo());
    }

    /*
     * Tests for hasConstraintViolation
     */

    public function testHasConstraintViolation()
    {
        // false
        $this->fixture = new ValidationReport(
            array( // results
                new ValidationResult(
                    $this->nodeFactory->createNamedNode('http://focusNode/'),
                    $this->nodeFactory->createNamedNode('http://resultPath/'),
                    new Severity('sh:Violation'),
                    $this->nodeFactory->createNamedNode('sh:MinMaxCountConstraintComponent'),
                    $this->nodeFactory->createNamedNode('http://value')
                )
            )
        );

        $this->assertTrue($this->fixture->hasConstraintViolation());
    }

    /*
     * Tests for hasConstraintWarning
     */

    public function testHasConstraintWarning()
    {
        // false
        $this->fixture = new ValidationReport(
            array( // results
                new ValidationResult(
                    $this->nodeFactory->createNamedNode('http://focusNode/'),
                    $this->nodeFactory->createNamedNode('http://resultPath/'),
                    new Severity('sh:Warning'),
                    $this->nodeFactory->createNamedNode('sh:MinMaxCountConstraintComponent'),
                    $this->nodeFactory->createNamedNode('http://value')
                )
            )
        );

        $this->assertTrue($this->fixture->hasConstraintWarning());
    }
}
