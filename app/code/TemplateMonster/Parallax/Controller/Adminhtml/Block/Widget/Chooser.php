<?php

namespace TemplateMonster\Parallax\Controller\Adminhtml\Block\Widget;

use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\View\LayoutFactory;
use Magento\Framework\Controller\Result\RawFactory;

/**
 * Chooser widget action.
 *
 * @package TemplateMonster\Parallax\Controller\Adminhtml\Block\Widget
 */
class Chooser extends Action
{
    /**
     * @var \Magento\Framework\View\LayoutFactory
     */
    protected $_layoutFactory;

    /**
     * @param LayoutFactory $layoutFactory
     * @param Context $context
     */
    public function __construct(
        LayoutFactory $layoutFactory,
        Context $context
    )
    {
        $this->_layoutFactory = $layoutFactory;
        parent::__construct($context);
    }

    /**
     * Chooser widget action.
     *
     * @return \Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {
        /** @var \Magento\Framework\View\Layout $layout */
        $layout = $this->_layoutFactory->create();

        $uniqId = $this->getRequest()->getParam('uniq_id');
        $pagesGrid = $layout->createBlock(
            'TemplateMonster\Parallax\Block\Adminhtml\Block\Widget\Chooser',
            '',
            ['data' => ['id' => $uniqId]]
        );

        /** @var \Magento\Framework\Controller\Result\Raw $resultRaw */
        $result = $this->resultFactory->create(ResultFactory::TYPE_RAW);
        $result->setContents($pagesGrid->toHtml());

        return $result;
    }
}
