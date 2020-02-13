<?php
namespace TemplateMonster\Megamenu\Model\Configurator\Row\Column;

use Magento\Framework\DataObject;

class Entity extends DataObject
{
    private $_entityData;

    public $rendererClass = 'Entity';

    public function __construct(
        array $data = []
    ) {
        parent::__construct($data);
    }

    public function init($entityData, $node) {
        $this->setNode($node);
        $this->_entityData = $entityData;
    }

    public function getRendererClass()
    {
        return $this->rendererClass;
    }
}