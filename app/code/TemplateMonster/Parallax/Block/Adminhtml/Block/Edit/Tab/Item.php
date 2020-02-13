<?php

namespace TemplateMonster\Parallax\Block\Adminhtml\Block\Edit\Tab;

use TemplateMonster\Parallax\Api\Data\BlockInterface;
use TemplateMonster\Parallax\Model\Config\Source\Type;
use TemplateMonster\Parallax\Model\ResourceModel\Block\Item\CollectionFactory as ItemCollectionFactory;
use Magento\Config\Model\Config\Source\Enabledisable;
use Magento\Backend\Block\Template\Context;
use Magento\Backend\Helper\Data;
use Magento\Backend\Block\Widget\Tab\TabInterface;
use Magento\Backend\Block\Widget\Grid\Extended;
use Magento\Framework\Registry;

/**
 * Block items tab.
 *
 * @package TemplateMonster\Parallax\Block\Adminhtml\Block\Edit\Tab
 */
class Item extends Extended implements TabInterface
{
    /**
     * @var Type
     */
    protected $_type;

    /**
     * @var Enabledisable
     */
    protected $_enabledisable;

    /**
     * @var ItemCollectionFactory
     */
    protected $_itemCollectionFactory;

    /**
     * @var Registry
     */
    protected $_coreRegistry;

    /**
     * Item constructor.
     *
     * @param Type                  $type
     * @param Enabledisable         $enabledisable
     * @param ItemCollectionFactory $itemCollectionFactory
     * @param Registry              $coreRegistry
     * @param Context               $context
     * @param Data                  $backendHelper
     * @param array                 $data
     */
    public function __construct(
        Type $type,
        Enabledisable $enabledisable,
        ItemCollectionFactory $itemCollectionFactory,
        Registry $coreRegistry,
        Context $context,
        Data $backendHelper,
        array $data = []
    ) {
        $this->_type = $type;
        $this->_enabledisable = $enabledisable;
        $this->_itemCollectionFactory = $itemCollectionFactory;
        $this->_coreRegistry = $coreRegistry;
        parent::__construct($context, $backendHelper, $data);
    }

    /**
     * @inheritdoc
     */
    protected function _construct()
    {
        parent::_construct();
        $this->setId('item');
        $this->setDefaultSort('item_id');
        $this->setUseAjax(true);
    }

    /**
     * @inheritdoc
     */
    public function getTabLabel()
    {
        return 'Parallax Block Items';
    }

    /**
     * @inheritdoc
     */
    public function getTabTitle()
    {
        return 'Parallax Block Items';
    }

    /**
     * @inheritdoc
     */
    public function canShowTab()
    {
        return true;
    }

    /**
     * @inheritdoc
     */
    public function isHidden()
    {
        return false;
    }

    /**
     * @inheritdoc
     */
    public function getMainButtonsHtml()
    {
        $html = parent::getMainButtonsHtml();
        if ($this->_getModel()->getId()) {
            $html .= $this->getAddItemButtonHtml();
        }

        return $html;
    }

    /**
     * @inheritdoc
     */
    public function getAddItemButtonHtml()
    {
        return $this->getChildHtml('add_item_button');
    }

    public function getGridUrl()
    {
        return $this->getUrl('*/*/grid', ['block_id' => $this->_getModel()->getId(), '_current' => true]);
    }

    /**
     * @inheritdoc
     */
    protected function _prepareColumns()
    {
        $this->addColumn('item_id', [
            'header' => __('Id'),
            'sortable' => true,
            'index' => 'item_id',
        ]);

        $this->addColumn('name', [
            'header' => __('Name'),
            'sortable' => true,
            'index' => 'name',
        ]);

        $this->addColumn('type', [
            'header' => __('Type'),
            'sortable' => true,
            'index' => 'type',
            'renderer' => 'Magento\Backend\Block\Widget\Grid\Column\Renderer\Options',
            'options' => $this->_type->toOptionArray(),
        ]);

        $this->addColumn('sort_order', [
            'header' => __('Sort Order'),
            'sortable' => true,
            'index' => 'sort_order',
        ]);

        $this->addColumn('status', [
            'header' => __('Status'),
            'sortable' => true,
            'index' => 'status',
            'renderer' => 'Magento\Backend\Block\Widget\Grid\Column\Renderer\Options',
            'options' => $this->_enabledisable->toOptionArray(),
        ]);

        $this->addColumn('action', [
            'header' => __('Action'),
            'sortable' => false,
            'filter' => false,
            'type' => 'action',
            'width' => '150px',
            'renderer' => 'TemplateMonster\Parallax\Block\Adminhtml\Block\Edit\Tab\Column\Renderer\Actions',
        ]);

        return parent::_prepareColumns();
    }

    /**
     * @inheritdoc
     */
    protected function _prepareCollection()
    {
        $collection = $this->_itemCollectionFactory->create();
        $collection->addFieldToSelect('*');
        $collection->addFieldToFilter('block_id', $this->_getModel()->getId());

        $this->setCollection($collection);

        return parent::_prepareCollection();
    }

    /**
     * @inheritdoc
     */
    protected function _prepareGrid()
    {
        $this->_prepareAddItemButton();

        return parent::_prepareGrid();
    }

    /**
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    protected function _prepareAddItemButton()
    {
        $this->setChild(
            'add_item_button',
            $this->getLayout()->createBlock(
                'Magento\Backend\Block\Widget\Button'
            )->setData(
                [
                    'label' => __('Add Item'),
                    'onclick' => sprintf('window.location="%s"',
                        $this->getUrl('*/blockItem/new', [
                            'block_id' => $this->_getModel()->getId()
                        ])
                    ),
                ]
            )
        );
    }

    /**
     * @inheritdoc
     */
    protected function _prepareMassaction()
    {
        $this->setMassactionIdField('item_id');
        $this->getMassactionBlock()->setTemplate('TemplateMonster_Parallax::widget/grid/massaction_extended.phtml');
        $this->getMassactionBlock()->setFormFieldName('ids');
        $this->getMassactionBlock()->setUseAjax(true);
        $this->getMassactionBlock()->setHideFormElement(true);

        $this->getMassactionBlock()->addItem(
            'enable',
            [
                'label' => __('Enable'),
                'url' => $this->getUrl('*/blockItem/massEnable'),
                'complete' => 'new Function("grid", "grid.reload()")'
            ]
        );

        $this->getMassactionBlock()->addItem(
            'disable',
            [
                'label' => __('Disable'),
                'url' => $this->getUrl('*/blockItem/massDisable'),
                'complete' => 'new Function("grid", "grid.reload()")'
            ]
        );

        $this->getMassactionBlock()->addItem(
            'delete',
            [
                'label' => __('Delete'),
                'url' => $this->getUrl('*/blockItem/massDelete'),
                'confirm' => __('Are you sure you want to delete selected block items?'),
                'complete' => 'new Function("grid", "grid.reload()")'
            ]
        );

        return parent::_prepareMassaction();
    }

    /**
     * @return BlockInterface
     */
    protected function _getModel()
    {
        return $this->_coreRegistry->registry('current_parallax_block');
    }
}
