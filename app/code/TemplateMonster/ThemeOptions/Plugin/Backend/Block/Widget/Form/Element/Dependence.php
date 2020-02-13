<?php

namespace TemplateMonster\ThemeOptions\Plugin\Backend\Block\Widget\Form\Element;

use Magento\Config\Model\Config\Structure\Element\Dependency\FieldFactory;

/**
 * Class Dependence
 *
 * @package TemplateMonster\ThemeOptions\Backend\Block\Widget\Form\Element
 */
class Dependence
{
    /**
     * @var FieldFactory
     */
    protected $_fieldFactory;

    /**
     * @var string
     */
    protected $_fieldName;

    /**
     * @var string
     */
    protected $_fieldNameFrom;

    /**
     * @var string
     */
    protected $_separator;

    /**
     * Dependence constructor.
     *
     * @param FieldFactory $fieldFactory
     * @param string       $fieldName
     * @param string       $fieldNameFrom
     * @param string       $separator
     */
    public function __construct(
        FieldFactory $fieldFactory,
        $fieldName,
        $fieldNameFrom,
        $separator
    )
    {
        $this->_fieldFactory = $fieldFactory;
        $this->_fieldName = $fieldName;
        $this->_fieldNameFrom = $fieldNameFrom;
        $this->_separator = $separator;
    }

    /**
     * @param $subject
     * @param $fieldName
     * @param $fieldNameFrom
     * @param $refField
     *
     * @return array
     */
    public function beforeAddFieldDependence($subject, $fieldName, $fieldNameFrom, $refField)
    {
        if ($fieldName === $this->_fieldName && $this->_fieldNameFrom === $fieldNameFrom) {
            $refField = $this->_fieldFactory->create(
                [
                    'fieldData' => [
                        'value' => (string) $refField,
                        'separator' => $this->_separator
                    ],
                    'fieldPrefix' => '',
                ]
            );
        }

        return [$fieldName, $fieldNameFrom, $refField];
    }
}