<?php
namespace TemplateMonster\Megamenu\Controller\Adminhtml\Category;

use TemplateMonster\Megamenu\Helper\Data;

class Block extends \Magento\Catalog\Controller\Adminhtml\Category
{
    protected $resultRawFactory;

    protected $layoutFactory;

    protected $_helper;

    protected $_staticBlocksSource;

    protected $resultJsonFactory;

    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Catalog\Model\Category\Attribute\Source\Page $staticBlocksSource,
        \Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory,
        \Magento\Framework\Controller\Result\RawFactory $resultRawFactory,
        \Magento\Framework\View\LayoutFactory $layoutFactory,
        Data $helper
    ) {
        parent::__construct($context);
        $this->_helper = $helper;
        $this->_staticBlocksSource = $staticBlocksSource;
        $this->resultJsonFactory = $resultJsonFactory;
        $this->resultRawFactory = $resultRawFactory;
        $this->layoutFactory = $layoutFactory;
    }

    public function execute()
    {
        $category = $this->_initCategory(true);
        if (!$category) {
            $resultRedirect = $this->resultRedirectFactory->create();
            return $resultRedirect->setPath('catalog/*/', ['_current' => true, 'id' => null]);
        }
        switch ($this->getRequest()->getParam('entity')) {
            case 'static_blocks' :
                $blocks = $this->_staticBlocksSource->getAllOptions();
                array_shift($blocks);
                return $this->resultJsonFactory->create()->setData($blocks);
                break;
            case 'subcategories' :
                $subcategories = $category->getChildrenCategories();
                $result = [];
                foreach ($subcategories as $subcategory) {
                    $result [] = ['id' => $subcategory->getEntityId(), 'name' => $subcategory->getName()];
                }
                return $this->resultJsonFactory->create()->setData($result);
                break;
            case 'products' :
                $resultRaw = $this->resultRawFactory->create();
                $block = $this->layoutFactory->create()->createBlock(
                    'TemplateMonster\Megamenu\Block\Adminhtml\Category\Tab\Products',
                    ''
                );
                $html = $block->toHtml() . $block->getProductWidgetScripts();
                return $resultRaw->setContents(
                    $html
                );
                break;
        }
        return null;
    }


}
