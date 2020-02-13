<?php
/**
 * @copyright   Copyright (c) http://www.manadev.com
 * @license     http://www.manadev.com/license  Proprietary License
 */
namespace Manadev\Core\Blocks\Adminhtml\ExtensionControl;

class Grid extends \Magento\Backend\Block\Widget\Grid\Extended
{
    /**
     * @var \Magento\Framework\Data\CollectionFactory
     */
    private $collectionFactory;

    /**
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Backend\Helper\Data $backendHelper
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Backend\Helper\Data $backendHelper,
        \Manadev\Core\Resources\ExtensionCollectionFactory $collectionFactory,
        array $data = []
    ) {
        $this->collectionFactory = $collectionFactory;
        parent::__construct($context, $backendHelper, $data);
    }

    /**
     * @return void
     */
    protected function _construct() {
        parent::_construct();
        $this->setId('extensionGrid');
        $this->setFilterVisibility(false);
        $this->setPagerVisibility(false);
    }


    /**
     * @return $this
     */
    protected function _prepareCollection() {
        $collection = $this->collectionFactory->create();
        $collection->setStore($this->getRequest()->getParam('store', 0));
        $this->setCollection($collection);

        parent::_prepareCollection();
        return $this;
    }

    protected function _prepareColumns() {
        $this->addColumn('title', [
            'header' => __('Extension Name'),
            'sortable' => false,
            'filter' => false,
            'index' => 'title',
            'width' => '200px',
            'align' => 'left',
            'renderer' => '\Manadev\Core\Blocks\Adminhtml\ExtensionControl\Feature\ExtensionNameColumn',
        ]);
        $this->addColumn('version', [
            'header' => __('Installed Version'),
            'sortable' => false,
            'filter' => false,
            'index' => 'version',
            'width' => '200px',
            'align' => 'left',
        ]);

        $this->addColumn('available_version', [
            'header' => __('Available Version'),
            'sortable' => false,
            'filter' => false,
            'index' => 'available_version',
            'width' => '200px',
            'align' => 'left',
        ]);
        $this->addColumn('is_enabled', [
            'header' => __('Status'),
            'sortable' => false,
            'filter' => false,
            'name' => 'is_enabled',
            'width' => '50px',
            'align' => 'left',
            'renderer' => '\Manadev\Core\Blocks\Adminhtml\ExtensionControl\Feature\IsEnabledColumn',
        ]);

        return parent::_prepareColumns();
    }

    public function getRowUrl($row) {
        return '';
    }
}