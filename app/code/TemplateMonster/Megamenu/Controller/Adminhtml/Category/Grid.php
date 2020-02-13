<?php
namespace TemplateMonster\Megamenu\Controller\Adminhtml\Category;

use TemplateMonster\Megamenu\Helper\Data;

class Grid extends \Magento\Catalog\Controller\Adminhtml\Category
{
    protected $resultRawFactory;

    protected $layoutFactory;

    protected $_helper;

    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Framework\Controller\Result\RawFactory $resultRawFactory,
        \Magento\Framework\View\LayoutFactory $layoutFactory,
        Data $helper
    ) {
        parent::__construct($context);
        $this->resultRawFactory = $resultRawFactory;
        $this->layoutFactory = $layoutFactory;
        $this->_helper = $helper;
    }

    public function execute()
    {
        $category = $this->_initCategory(true);
        if (!$category) {
            $resultRedirect = $this->resultRedirectFactory->create();
            return $resultRedirect->setPath('catalog/*/', ['_current' => true, 'id' => null]);
        }
        $resultRaw = $this->resultRawFactory->create();
        $html = $this->layoutFactory->create()->createBlock(
            'TemplateMonster\Megamenu\Block\Adminhtml\Category\Tab\Products',
            ''
        )->toHtml();
        $html = $this->_helper->hackGrid($html);
        return $resultRaw->setContents(
            $html
        );
    }
}
