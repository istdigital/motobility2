<?php

namespace Yereone\Testimonials\Controller\Adminhtml\Testimonial;

use Magento\Backend\App\Action;
use Magento\Framework\Exception\LocalizedException;

class Save extends Action
{
    /**
     * @var \Yereone\Testimonials\Model\TestimonialFactory
     */
    protected $testimonialFactory;

    /**
     * @var \Yereone\Testimonials\Helper\Data
     */
    protected $helper;

    /**
     * @param Action\Context $context
     * @param \Yereone\Testimonials\Model\TestimonialFactory $testimonialFactory
     */
    public function __construct(
        Action\Context $context,
        \Yereone\Testimonials\Model\TestimonialFactory $testimonialFactory,
        \Yereone\Testimonials\Helper\Data $helper
    ) {
        $this->testimonialFactory = $testimonialFactory;
        $this->helper = $helper;
        parent::__construct($context);
    }

    /**
     * Save action
     *
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @return \Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {
        /** @var \Magento\Backend\Model\View\Result\Redirect $resultRedirect */
        $resultRedirect = $this->resultRedirectFactory->create();
        $data = $this->getRequest()->getPostValue();
        $model = $this->testimonialFactory->create();
        if ($data) {
            if (isset($data['id'])) {
                $model = $model->load($data['id']);
            }
            $imageRequest = $this->getRequest()->getFiles('image');
            $data = $this->helper->imageUpload($data, $imageRequest);

            $model->setData($data);
            try {
                $model->save();
                $this->messageManager->addSuccess(__('You saved the Testimonial.'));

                if ($this->getRequest()->getParam('back')) {
                    return $resultRedirect->setPath('*/*/edit', ['id' => $model->getId()]);
                }
                return $resultRedirect->setPath('*/*/');
            } catch (LocalizedException $e) {
                $this->messageManager->addError($e->getMessage());
            } catch (\Exception $e) {
                $this->messageManager->addException($e, __('Something went wrong while saving the testimonial.'));
            }

            return $resultRedirect->setPath('*/*/edit', ['id' => $this->getRequest()->getParam('id')]);
        }
        return $resultRedirect->setPath('*/*/');
    }
}
