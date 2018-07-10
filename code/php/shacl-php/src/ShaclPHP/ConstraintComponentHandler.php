<?php

namespace ShaclPHP;

use Knorke\ResourceGuy;
use Knorke\ResourceGuyHelper;
use Saft\Rdf\CommonNamespaces;
use Saft\Rdf\NodeFactory;
use ShaclPHP\ConstraintComponentHandlerFactory;

abstract class ConstraintComponentHandler
{
    protected $commonNamespaces;
    protected $constraintComponentHandlerFactory;
    protected $nodeFactory;
    protected $resourceGuyHelper;

    /**
     * @param NodeFactory $nodeFactory
     * @param CommonNamespaces $commonNamespaces
     * @param ResourceGuyHelper $resourceGuyHelper
     * @param ConstraintComponentHandlerFactory $constraintComponentHandlerFactory
     */
    public function __construct(
        NodeFactory $nodeFactory,
        CommonNamespaces $commonNamespaces,
        ResourceGuyHelper $resourceGuyHelper,
        ConstraintComponentHandlerFactory $constraintComponentHandlerFactory
    ) {
        $this->commonNamespaces = $commonNamespaces;
        $this->constraintComponentHandlerFactory = $constraintComponentHandlerFactory;
        $this->nodeFactory = $nodeFactory;
        $this->resourceGuyHelper = $resourceGuyHelper;
    }

    /**
     * @param ResourceGuy $shape
     * @return array Array of ValidationResult
     */
    abstract public function handle(ResourceGuy $shape) : array;
}
