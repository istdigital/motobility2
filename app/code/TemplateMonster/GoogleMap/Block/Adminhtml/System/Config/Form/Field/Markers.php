<?php
/**
 * Copyright © 2016 TemplateMonster. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace TemplateMonster\GoogleMap\Block\Adminhtml\System\Config\Form\Field;

/**
 * Backend system config array field renderer
 */
class Markers extends \Magento\Config\Block\System\Config\Form\Field\FieldArray\AbstractFieldArray
{

    protected $_getMarkerImages;

    /**
     * @var \Magento\Framework\Data\Form\Element\Factory
     */
    protected $elementFactory;

    /**
     * @param \Magento\Backend\Block\Template\Context $context
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Framework\Data\Form\Element\Factory $elementFactory,
        array $data = []
    )
    {
        $this->elementFactory = $elementFactory;
        parent::__construct($context,$data);
    }

    protected function _construct()
    {
        $this->_addButtonLabel = __('Add Marker');
        parent::_construct();
    }

    protected function _prepareToRender() {
        $this->addColumn('icon',        ['label' => __('Icon'), 'size' => 3]);
        $this->addColumn('coordinates', ['label' => __('Coordinates')]);
        $this->addColumn('infowindow',  ['label' => __('Infowindow')]);
        $this->_addButtonLabel = __('Add Marker');
        $this->_addAfter = false;
    }


    /**
     * Render array cell for prototypeJS template
     *
     * @param string $columnName
     * @return string
     * @throws \Exception
     */
    public function renderCellTemplate($columnName)
    {
        if (empty($this->_columns[$columnName])) {
            throw new \Exception('Wrong column name specified.');
        }

        if ($columnName == 'infowindow' && isset($this->_columns[$columnName])) {

            $element = $this->elementFactory->create('textarea');
            $element->setId("infowindow")->setName("infowindow");
            $element->setForm($this->getForm())->setName($this->_getCellInputElementName($columnName))->setHtmlId($this->_getCellInputElementId('<%- _id %>', $columnName));
            return $element->getElementHtml();

        } elseif ($columnName == 'coordinates' && isset($this->_columns[$columnName])) {

            $element = $this->elementFactory->create('textarea');
            $element->setId("coordinates")->setName("coordinates");
            $element->setForm($this->getForm())->setName($this->_getCellInputElementName($columnName))->setHtmlId($this->_getCellInputElementId('<%- _id %>', $columnName));
            return $element->getElementHtml();

        } elseif ($columnName == 'icon' && isset($this->_columns[$columnName])) {

            $element = $this->elementFactory->create('textarea');
            $element->setId("icon")->setName("icon");
            $element->setForm($this->getForm())->setName($this->_getCellInputElementName($columnName))->setHtmlId($this->_getCellInputElementId('<%- _id %>', $columnName));
            return $element->getElementHtml();
        } else {
            return parent::renderCellTemplate($columnName);
        }

    }
}
