<?php
namespace TemplateMonster\Megamenu\Model;

use Magento\Framework\DataObject;

class Configurator extends DataObject
{
    private $_rows = array();

    private $_rowFactory;

    public function __construct(
        \TemplateMonster\Megamenu\Model\Configurator\RowFactory $rowFactory,
        array $data = []
    ) {
        parent::__construct($data);
        $this->_rowFactory = $rowFactory;
    }

    public function init($node)
    {
        $this->_rows = [];
        if ($configurator = $node->getMmConfigurator()) {
            $configurator = \Zend_Json::decode($configurator);
            foreach ($configurator as $rowData) {
                $row = $this->_rowFactory->create();
                $row->init($rowData, $node);
                $this->_rows []= $row;
            }
        }
    }

    public function getRows()
    {
        return $this->_rows;
    }
}