<?php
/**
 * Mageplaza
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Mageplaza.com license that is
 * available through the world-wide-web at this URL:
 * https://www.mageplaza.com/LICENSE.txt
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade this extension to newer
 * version in the future.
 *
 * @category    Mageplaza
 * @package     Mageplaza_Blog
 * @copyright   Copyright (c) Mageplaza (https://www.mageplaza.com/)
 * @license     https://www.mageplaza.com/LICENSE.txt
 */

namespace Mageplaza\Blog\Controller\Ajax;

use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\View\Result\PageFactory;
use Mageplaza\Blog\Helper\Data;


/**
 * Class Index
 * @package Mageplaza\Blog\Controller\Post
 */
class Index extends Action
{
    /**
     * @var \Magento\Framework\View\Result\PageFactory
     */
    public $resultPageFactory;

    /**
     * @var Data
     */
    protected $_helperBlog;


    protected $_urlInterface;

    /**
     * Index constructor.
     *
     * @param Context $context
     * @param PageFactory $resultPageFactory
     * @param Data $helperData
     */
    public function __construct(
        Context $context,
        PageFactory $resultPageFactory,
        Data $helperData,
        \Magento\Framework\UrlInterface $urlInterface
    ) {
        $this->_helperBlog = $helperData;
        $this->_urlInterface = $urlInterface;
        $this->resultPageFactory = $resultPageFactory;

        parent::__construct($context);
    }

    /**
     * @return \Magento\Framework\View\Result\Page
     */
    public function execute()
    {
        $collection = $this->_helperBlog->getPostList()->setOrder('post_id', 'desc');
        $collection->getSelect()->limit(3);

        $return = [];

        #$m = $this->_urlInterface->getBaseUrl(['_type' => \Magento\Framework\UrlInterface::URL_TYPE_MEDIA]).'mageplaza/blog/post/';

        foreach ($collection as $post) {
            $return[] = [
                'name' => $post->getName(),
                'url' => $post->getUrl(),
                'publish_date' => date('F d, Y',strtotime($post->getPublishDate())),
                'short_description' => $post->getShortDescription(),
                'image' => $this->_helperBlog->getImageHelper()->resizeImage($post->getImage(), '327x261', 'post',false),
            ];            
        }
        echo json_encode($return);
    }
}
