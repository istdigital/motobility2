<?php

namespace Yereone\Testimonials\Controller\Adminhtml\Testimonial;

class Index extends \Magento\Backend\App\Action
{
    /**
     * @return \Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {
        return  $this->resultFactory->create('page');
    }
}
