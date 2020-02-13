<?php

namespace TemplateMonster\ThemeOptions\Model\Export;

use Magento\ImportExport\Model\Export\Adapter\AbstractAdapter;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Config\Model\ResourceModel\Config\Data\CollectionFactory as ConfigCollectionFactory;
use Magento\ImportExport\Model\Export\Factory;
use Magento\ImportExport\Model\ResourceModel\CollectionByPagesIteratorFactory;
use Magento\ImportExport\Model\Export\AbstractEntity;
use Magento\Framework\App\Config\ScopeConfigInterface;

/**
 * Settings export model
 *
 * @package TemplateMonster\ThemeOptions\Model\Export
 */
class Settings extends AbstractEntity
{
    /**
     * @var ConfigCollectionFactory
     */
    protected $_configCollectionFactory;

    /**
     * @inheritdoc
     */
    protected $_pageSize = 100;

    /**
     * Metadata config path.
     */
    const PATH_METADATA = 'theme_options/metadata';

    /**
     * Settings constructor.
     *
     * @param ConfigCollectionFactory          $configCollectionFactory
     * @param ScopeConfigInterface             $scopeConfig
     * @param StoreManagerInterface            $storeManager
     * @param Factory                          $collectionFactory
     * @param CollectionByPagesIteratorFactory $resourceColFactory
     * @param array                            $data
     */
    public function __construct(
        ConfigCollectionFactory $configCollectionFactory,
        ScopeConfigInterface $scopeConfig,
        StoreManagerInterface $storeManager,
        Factory $collectionFactory,
        CollectionByPagesIteratorFactory $resourceColFactory,
        array $data = []
    ) {
        $this->_configCollectionFactory = $configCollectionFactory;
        parent::__construct(
            $scopeConfig, $storeManager, $collectionFactory,
            $resourceColFactory, $data
        );
    }

    /**
     * @inheritdoc
     */
    public function export()
    {
        $writer = $this->getWriter();
        $writer->setHeaderCols($this->_getHeaderColumns());
        $this->_exportCollectionByPages($this->_getEntityCollection());
        $this->_addExportMetadata($writer);

        return $writer->getContents();
    }

    /**
     * @inheritdoc
     */
    public function exportItem($item)
    {
        /** @var \Magento\Framework\App\Config\Value $item */
        if (empty($item->getValue()) || $item->getPath() == self::PATH_METADATA) {
            return;
        }

        $this->getWriter()->writeRow([
            'scope' => $item->getScope(),
            'scope_id' => $item->getScopeId(),
            'path' => $item->getPath(),
            'value' => $item->getValue()
        ]);
    }

    /**
     * @inheritdoc
     */
    public function getEntityTypeCode()
    {
        return 'theme_options';
    }

    /**
     * @inheritdoc
     */
    protected function _getHeaderColumns()
    {
        return array('scope', 'scope_id', 'path', 'value');
    }

    /**
     * @inheritdoc
     */
    protected function _getEntityCollection()
    {
        /** @var \Magento\Config\Model\ResourceModel\Config\Data\Collection $collection */
        $collection = $this->_configCollectionFactory->create();
        $collection->addPathFilter('theme_options');

        return $collection;
    }

    /**
     * Add export metadata.
     *
     * @param AbstractAdapter $writer
     */
    protected function _addExportMetadata(AbstractAdapter $writer)
    {
        $dateTime = new \DateTime('now', new \DateTimeZone('UTC'));
        $writer->writeRow([
            'scope' => 'default',
            'scope_id' => '0',
            'path' => self::PATH_METADATA,
            'value' => serialize(['generation_time' => $dateTime->getTimestamp()])
        ]);
    }
}