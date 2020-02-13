<?php
/**
 * Mage-World
 *
 *  @category    Mage-World
 *  @package     MW
 *  @author      Mage-world Developer
 *
 *  @copyright   Copyright (c) 2018 Mage-World (https://www.mage-world.com/)
 */

namespace MW\Downloads\Block\Adminhtml\Downloads\Edit;

class ViewDocument extends \Magento\Backend\Block\Template
{
    protected $context;

    public function __construct(\Magento\Backend\Block\Template\Context $context, array $data = [])
    {
        $this->context = $context;
        parent::__construct($context, $data);
    }

    /**
     * {@inheritdoc}
     */
    protected function _toHtml()
    {
        
        if($id = $this->getRequest()->getParam('id')){
            $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
            $download = $objectManager->create('MW\Downloads\Model\Downloads')->load($id);
            if ($download && $download->getId()) {

                $_urlBuilder = $this->context->getUrlBuilder();


                $file = $_urlBuilder->getBaseUrl(['_type' => \Magento\Framework\UrlInterface::URL_TYPE_MEDIA]) . $download->getDocument();
                $html = null;
                $html .= "<a target='_blank' href='$file' style='margin-left: calc( (100%) * 0.26);margin-bottom: 25px;display: inline-block;'>";
                    $html .= "View File";
                $html .= "</a>";
                #$html .= "<label style='margin-left: 50px;'>";
                #$html .= "<input type='checkbox' name='remove_file'> Remove File";
                #$html .= "</label>";
                return $html;
            }
        }

        return "";
    }
}