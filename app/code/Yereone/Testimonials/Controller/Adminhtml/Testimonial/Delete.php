<?php

namespace Yereone\Testimonials\Controller\Adminhtml\Testimonial;

use Magento\Backend\App\Action;

class Delete extends Action
{
    /**
     * @var \Yereone\Testimonials\Model\TestimonialFactory
     */
    protected $testimonialFactory;

    /**
     * @param Action\Context $context
     * @param \Yereone\Testimonials\Model\TestimonialFactory $testimonialFactory
     */
    public function __construct(
        Action\Context $context,
        \Yereone\Testimonials\Model\TestimonialFactory $testimonialFactory
    ) {
        $this->testimonialFactory = $testimonialFactory;
        parent::__construct($context);
    }

    /**
     * Delete action
     *
     * @return \Magento\Backend\Model\View\Result\Redirect
     */
    public function execute()
    {
        // check if we know what should be deleted
        $id = $this->getRequest()->getParam('id');
        /** @var \Magento\Backend\Model\View\Result\Redirect $resultRedirect */
        $resultRedirect = $this->resultRedirectFactory->create();
        if ($id) {
            try {
                $model = $this->testimonialFactory->create();
                $model->load($id);
                $model->delete();
                // display success message
                $this->messageManager->addSuccess(__('Testimonial has been deleted.'));
                return $resultRedirect->setPath('*/*/');
            } catch (\Exception $e) {
                // display error message
                $this->messageManager->addError($e->getMessage());
                // go back to edit form
                return $resultRedirect->setPath('*/*/edit', ['page_id' => $id]);
            }
        }
        // display error message
        $this->messageManager->addError(__('We can\'t find a testimonial to delete.'));
        // go to grid
        return $resultRedirect->setPath('*/*/');
    }
}
