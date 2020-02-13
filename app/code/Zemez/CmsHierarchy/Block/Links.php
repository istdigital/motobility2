<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Zemez\CmsHierarchy\Block;

use Magento\Framework\View\Element\Template;

/**
 * Cms page content block
 *
 * @api
 * @since 100.0.2
 */
class Links extends Template
{

    public function __construct(Template\Context $context,
        array $data = [])
    {
        parent::__construct($context, $data);
    }
    protected function _afterToHtml($html)
    {

        $links = $this->getData('default');
        if(count($links)){
            $html = "<div class='left-cms-links'>";
            $html .= "<ul>";
            foreach ($links as $link) {
                $html .= "<li>";    
                $html .= "<a href='".trim($this->getUrl($link['url']),"/")."' title='".$link['title']."'>";    
                $html .= $link['title'];    
                $html .= "</a>";    
                $html .= "</li>";    
            }
            $html .= "</ul>";
            $html .= "</div>";
            $html .= "<script type='text/javascript'>
            require(['jquery'], function($){ 
                $('div.left-cms-links a[href=\''+window.location.href+'\']').parent().addClass('active');
                $('div.left-cms-links a[href=\''+window.location.href+'/\']').parent().addClass('active');
                });
            </script>";
        }
        return $html;
    }
}