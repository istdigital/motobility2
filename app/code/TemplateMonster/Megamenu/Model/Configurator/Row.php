<?php
namespace TemplateMonster\Megamenu\Model\Configurator;

use Magento\Framework\DataObject;

class Row extends DataObject
{
    private $_columns = array();

    private $_columnFactory;

    private $_rowData;

    public function __construct(
        \TemplateMonster\Megamenu\Model\Configurator\Row\ColumnFactory $columnFactory,
        array $data = []
    ) {
        parent::__construct($data);
        $this->_columnFactory = $columnFactory;
    }

    public function init($rowData, $node) {
        $this->setNode($node);
        $this->_rowData = $rowData;
        foreach ($rowData as $columnData) {
            $column = $this->_columnFactory->create();
            $column->init($columnData, $node);
            $this->_columns []= $column;
        }
    }

    public function getColumns()
    {
        return $this->_columns;
    }

}