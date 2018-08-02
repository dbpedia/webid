<?php

namespace ShaclPHP;

use Knorke\ResourceGuyHelper;
use Saft\Rdf\CommonNamespaces;
use Saft\Rdf\NodeFactory;
use Saft\Store\Store;
use ShaclPHP\ConstraintComponentHandler\AndConstraintComponentHandler;
use ShaclPHP\ConstraintComponentHandler\HasValueConstraintComponentHandler;
use ShaclPHP\ConstraintComponentHandler\MinMaxCountConstraintComponentHandler;
use ShaclPHP\ConstraintComponentHandler\OrConstraintComponentHandler;

/**
 * This class helps by the creation of ConstraintComponentHandler by reducing the amount of
 * dependencies one has to handle.
 */
class ConstraintComponentHandlerFactory
{
    protected $graphUri;
    protected $handler;
    protected $nodeFactory;
    protected $resourceGuyHelper;
    protected $store;

    /**
     * @param NodeFactory $nodeFactory
     * @param CommonNamespaces $commonNamespaces
     * @param ResourceGuyHelper $resourceGuyHelper
     */
    public function __construct(
        NodeFactory $nodeFactory,
        CommonNamespaces $commonNamespaces,
        ResourceGuyHelper $resourceGuyHelper
    ) {
        $this->commonNamespaces = $commonNamespaces;
        $this->nodeFactory = $nodeFactory;
        $this->resourceGuyHelper = $resourceGuyHelper;

        $this->handler = array();
    }

    /**
     * @param string $name Name of the ConstraintComponentHandler instance (e.g. MinCountConstraintComponentHandler).
     * @return ConstraintComponentHandler|null
     */
    public function create(string $name)
    {
        if (isset($this->handler[$name])) {
            return $this->handler[$name];
        }

        if ('AndConstraintComponentHandler' == $name) {
            $this->handler[$name] = new AndConstraintComponentHandler(
                $this->nodeFactory,
                $this->commonNamespaces,
                $this->resourceGuyHelper,
                $this
            );

        } else if ('MinMaxCountConstraintComponentHandler' == $name) {
            $this->handler[$name] = new MinMaxCountConstraintComponentHandler(
                $this->nodeFactory,
                $this->commonNamespaces,
                $this->resourceGuyHelper,
                $this
            );

        } elseif ('HasValueConstraintComponentHandler' == $name) {
            $this->handler[$name] = new HasValueConstraintComponentHandler(
                $this->nodeFactory,
                $this->commonNamespaces,
                $this->resourceGuyHelper,
                $this
            );

        } elseif ('OrConstraintComponentHandler' == $name) {
            $this->handler[$name] = new OrConstraintComponentHandler(
                $this->nodeFactory,
                $this->commonNamespaces,
                $this->resourceGuyHelper,
                $this
            );

        } else { // nothing found
            $this->handler[$name] = null;
        }

        return $this->handler[$name];
    }

    /**
     * @param array|ResourceGuy $arrayLikeStructure
     * @return null|string
     */
    public function determineConstraintName($arrayLikeStructure)
    {
        if (isset($arrayLikeStructure['sh:hasValue'])) {
            return 'HasValueConstraintComponentHandler';

        } elseif (isset($arrayLikeStructure['sh:minCount'])
            || isset($arrayLikeStructure['sh:maxCount'])) {
            return 'MinMaxCountConstraintComponentHandler';

        } elseif (isset($arrayLikeStructure['sh:or'])) {
            return 'OrConstraintComponentHandler';
        }

        return null;
    }
}
