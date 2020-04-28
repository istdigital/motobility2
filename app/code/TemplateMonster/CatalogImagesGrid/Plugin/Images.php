<?php

/**
 *
 * Copyright © 2015 TemplateMonster. All rights reserved.
 * See COPYING.txt for license details.
 *
 */

namespace TemplateMonster\CatalogImagesGrid\Plugin;

use Magento\Framework\App\RequestInterface;
use \Magento\Store\Model\StoreManagerInterface;

class Images
{
    /**
     * @var RequestInterface
     */
    protected $_request;

    /**
     * @var StoreManagerInterface
     */
    protected $_storeManager;

    /**
     * Images constructor.
     * @param RequestInterface $request
     * @param StoreManagerInterface $storeManager
     */
    public function __construct(
        RequestInterface $request,
        StoreManagerInterface $storeManager
    ) {
        $this->_request = $request;
        $this->_storeManager = $storeManager;
    }

    public function aroundGetImageHtmlDeclaration(\Magento\Cms\Helper\Wysiwyg\Images $subject,
                                                  \Closure $proceed,
                                                  $filename,
                                                  $renderAsTag = false
    ) {
        if ($this->_request->getParam("refineurl")) {
            $fileurl = $subject->getCurrentUrl() . $filename;
            $mediaUrl = $this->_storeManager->getStore()->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_MEDIA);
            $mediaPath = str_replace($mediaUrl, '', $fileurl);

            return $mediaPath;
        }

        $returnValue = $proceed($filename, $renderAsTag);
        return $returnValue;
    }
}
