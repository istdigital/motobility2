<?php

namespace TemplateMonster\SampleDataInstaller\Model\Import;

use Magento\Framework\ObjectManagerInterface;

/**
 * Class ImportFactory
 *
 * @package TemplateMonster\SampleDataInstaller\Model\Import
 */
class ImportFactory
{
    /**
     * @var array
     */
    protected $_types = [
        'cms_page' => CmsPage::class,
        'cms_block' => CmsBlock::class,
        'widget' => Widget::class
    ];

    /**
     * @var ObjectManagerInterface
     */
    protected $_objectManager;

    /**
     * ImportFactory constructor.
     *
     * @param ObjectManagerInterface $objectManager
     */
    public function __construct(ObjectManagerInterface $objectManager)
    {
        $this->_objectManager = $objectManager;
    }

    /**
     * @param string $type
     * @param array  $data
     *
     * @return ImportInterface
     */
    public function create($type, array $data = [])
    {
        if (!array_key_exists($type, $this->_types)) {
            throw new \InvalidArgumentException('Invalid import type.');
        }

        $className = $this->_types[$type];
        $import = $this->_objectManager->create($className, ['data' => $data]);
        if (!($import instanceof ImportInterface)) {
            throw new \InvalidArgumentException(sprintf('%s doesn\'t implement %s', $className, ImportInterface::class));
        }


        return $import;
    }
}