<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Zemez\CmsHierarchy\Block;

use Magento\Store\Model\ScopeInterface;

/**
 * Cms page content block
 *
 * @api
 * @since 100.0.2
 */
class Page extends \Magento\Cms\Block\Page
{
	/**
     * Prepare global layout
     *
     * @return $this
     */
    protected function _prepareLayout()
    {
        $page = $this->getPage();
        $this->_addBreadcrumbs($page);
        $this->pageConfig->addBodyClass('cms-' . $page->getIdentifier());
        $metaTitle = $page->getMetaTitle();
        $this->pageConfig->getTitle()->set($metaTitle ? $metaTitle : $page->getTitle());
        $this->pageConfig->setKeywords($page->getMetaKeywords());
        $this->pageConfig->setDescription($page->getMetaDescription());

       
        return parent::_prepareLayout();
    }

	/**
     * Prepare breadcrumbs
     *
     * @param \Magento\Cms\Model\Page $page
     * @throws \Magento\Framework\Exception\LocalizedException
     * @return void
     */
    protected function _addBreadcrumbs(\Magento\Cms\Model\Page $page){
    	
    	$homePageIdentifier = $this->_scopeConfig->getValue(
            'web/default/cms_home_page',
            ScopeInterface::SCOPE_STORE
        );
        $homePageDelimiterPosition = strrpos($homePageIdentifier, '|');
        if ($homePageDelimiterPosition) {
            $homePageIdentifier = substr($homePageIdentifier, 0, $homePageDelimiterPosition);
        }
        $noRouteIdentifier = $this->_scopeConfig->getValue(
            'web/default/cms_no_route',
            ScopeInterface::SCOPE_STORE
        );
        $noRouteDelimiterPosition = strrpos($noRouteIdentifier, '|');
        if ($noRouteDelimiterPosition) {
            $noRouteIdentifier = substr($noRouteIdentifier, 0, $noRouteDelimiterPosition);
        }
        if ($this->_scopeConfig->getValue('web/default/show_cms_breadcrumbs', ScopeInterface::SCOPE_STORE)
            && ($breadcrumbsBlock = $this->getLayout()->getBlock('breadcrumbs'))
            && $page->getIdentifier() !== $homePageIdentifier
            && $page->getIdentifier() !== $noRouteIdentifier
        ) {
            $breadcrumbsBlock->addCrumb(
                'home',
                [
                    'label' => __('Home'),
                    'title' => __('Go to Home Page'),
                    'link' => $this->_storeManager->getStore()->getBaseUrl()
                ]
            );

        	$routes = explode("/",$page->getIdentifier());
        	if(count($routes) > 1){
        		array_pop($routes);
        		foreach ($routes as $path) {
        			$p = $this->_pageFactory->create()->load($path);
        			if($p){
        				$breadcrumbsBlock->addCrumb(
			                $path,
			                [
			                    'label' => $p->getTitle(),
			                    'title' => __('Go to %1',$p->getTitle()),
			                    'link' => $this->_urlBuilder->getUrl($path)
			                ]
			            );


			            $pageMainTitle = $this->getLayout()->getBlock('page.main.title');
				        if ($pageMainTitle) {
				            // Setting empty page title if content heading is absent
				            $cmsTitle = $p->getContentHeading() ?: ' ';
				            $pageMainTitle->setPageTitle($this->escapeHtml($cmsTitle));
				        }
        			}
        		}
        	}

            $breadcrumbsBlock->addCrumb('cms_page', ['label' => $page->getTitle(), 'title' => $page->getTitle()]);
        }
    }
    protected function _afterToHtml($html)
    {
        $script = "<script type='text/javascript'>
            require(['jquery'], function($){ 
                $('ul.static-pages-links a[href=\''+window.location.href+'\']').addClass('active');
                $('ul.static-pages-links a[href=\''+window.location.href+'/\']').addClass('active');
                });
            </script>";
        return $html . $script;
    }
}