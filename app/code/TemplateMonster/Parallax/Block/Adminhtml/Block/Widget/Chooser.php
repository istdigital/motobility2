<?php

namespace TemplateMonster\Parallax\Block\Adminhtml\Block\Widget;

use Magento\Framework\Exception\NoSuchEntityException;
use TemplateMonster\Parallax\Api\BlockRepositoryInterface;
use TemplateMonster\Parallax\Model\ResourceModel\Block\CollectionFactory as BlockCollectionFactory;
use Magento\Framework\Data\Form\Element\AbstractElement;
use Magento\Backend\Block\Template\Context;
use Magento\Backend\Helper\Data;
use Magento\Backend\Block\Widget\Grid\Extended;

/**
 * Parallax widget chooser.
 *
 * @package TemplateMonster\Parallax\Block\Adminhtml\Block\Widget
 */
class Chooser extends Extended
{
    /**
     * @var BlockRepositoryInterface
     */
    protected $_blockRepository;

    /**
     * @var BlockCollectionFactory
     */
    protected $_blockCollectionFactory;

    /**
     * Chooser constructor.
     *
     * @param BlockRepositoryInterface $blockRepository
     * @param BlockCollectionFactory   $blockCollectionFactory
     * @param Context                  $context
     * @param Data                     $backendHelper
     * @param array                    $data
     */
    public function __construct(
        BlockRepositoryInterface $blockRepository,
        BlockCollectionFactory $blockCollectionFactory,
        Context $context,
        Data
        $backendHelper,
        array $data = []
    ) {
        $this->_blockRepository = $blockRepository;
        $this->_blockCollectionFactory = $blockCollectionFactory;
        parent::__construct($context, $backendHelper, $data);
    }

    /**
     * @inheritdoc
     */
    protected function _construct()
    {
        parent::_construct();
        $this->setDefaultSort('block_id');
        $this->setDefaultDir('ASC');
        $this->setUseAjax(true);
    }

    /**
     * @param AbstractElement $element
     *
     * @return AbstractElement
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function prepareElementHtml(AbstractElement $element)
    {
        $uniqId = $this->mathRandom->getUniqueHash($element->getId());
        $sourceUrl = $this->getUrl('parallax/block_widget/chooser', ['uniq_id' => $uniqId]);

        $chooser = $this->getLayout()->createBlock(
            'Magento\Widget\Block\Adminhtml\Widget\Chooser'
        )->setElement(
            $element
        )->setConfig(
            $this->getConfig()
        )->setFieldsetId(
            $this->getFieldsetId()
        )->setSourceUrl(
            $sourceUrl
        )->setUniqId(
            $uniqId
        );

        if ($element->getValue()) {
            try {
                $block = $this->_blockRepository->getById($element->getValue());
                if ($block->getId()) {
                    $chooser->setLabel($this->escapeHtml($block->getName()));
                }
            } catch (NoSuchEntityException $e) {
                // ignore error
            }
        }

        $element->setData('after_element_html', $chooser->toHtml());

        return $element;
    }

    /**
     * @inheritdoc
     */
    public function getGridUrl()
    {
        return $this->getUrl('parallax/block_widget/chooser', ['_current' => true]);
    }

    /**
     * @inheritdoc
     */
    public function getRowClickCallback()
    {
        $chooserJsObject = $this->getId();
        $js = '
            function (grid, event) {
                var trElement = Event.findElement(event, "tr");
                var blockId = trElement.down("td").innerHTML.replace(/^\s+|\s+$/g,"");
                var blockName = trElement.down("td").next().innerHTML;
                ' .
            $chooserJsObject .
            '.setElementValue(blockId);
                ' .
            $chooserJsObject .
            '.setElementLabel(blockName);
                ' .
            $chooserJsObject .
            '.close();
            }
        ';

        return $js;
    }

    /**
     * @inheritdoc
     */
    protected function _prepareCollection()
    {
        $this->setCollection($this->_blockCollectionFactory->create());

        return parent::_prepareCollection();
    }

    /**
     * @inheritdoc
     */
    protected function _prepareColumns()
    {
        $this->addColumn('chooser_id', [
                'header' => __('ID'),
                'align' => 'right',
                'index' => 'block_id',
                'width' => 50
        ]);

        $this->addColumn('chooser_name', [
            'header' => __('Name'),
            'align' => 'left',
            'index' => 'name'
        ]);

        $this->addColumn('chooser_status', [
                'header' => __('Status'),
                'index' => 'status',
                'type' => 'options',
                'options' => [0 => __('Disabled'), 1 => __('Enabled')]
        ]);

        return parent::_prepareColumns();
    }
}