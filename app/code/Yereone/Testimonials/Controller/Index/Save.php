<?php

namespace Yereone\Testimonials\Controller\Index;

use Magento\Framework\Exception\LocalizedException;
use Yereone\Testimonials\Model\Mail;

class Save extends \Magento\Framework\App\Action\Action
{
    /**
     * @var \Yereone\Testimonials\Helper\Data
     */
    protected $helper;

    /**
     * @var MailInterface
     */
    private $mail;

    /**
     * @var \Yereone\Testimonials\Model\TestimonialFactory
     */
    protected $testimonialFactory;

    /**
     * @param \Magento\Framework\App\Action\Context $context
     * @param \Yereone\Testimonials\Helper\Data $helper
     */
    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Yereone\Testimonials\Helper\Data $helper,
        Mail $mail,
        \Yereone\Testimonials\Model\TestimonialFactory $testimonialFactory
    ) {
        $this->helper = $helper;
        $this->mail = $mail;
        $this->testimonialFactory = $testimonialFactory;
        parent::__construct($context);
    }

    /**
     * @return void
     */
    public function execute()
    {
        /** @var \Magento\Backend\Model\View\Result\Redirect $resultRedirect */
        $resultRedirect = $this->resultRedirectFactory->create();

        $data = $this->getRequest()->getPostValue();
        $model = $this->testimonialFactory->create();
        if ($data) {

            // $imageRequest = $this->getRequest()->getFiles('image');
            // $data = $this->helper->imageUpload($data, $imageRequest);

            $data['status_id'] = 2;
            $data['rating'] = 100;
            $data['title'] = substr($data['testimonial_content'],0,20);
            $model->setData($data);


            try {
                $model->save();

                $this->sendEmail($data);

                return $this->resultRedirectFactory->create()->setPath('testimonial-confirmation');

            } catch (LocalizedException $e) {
                $this->messageManager->addError($e->getMessage());
            } catch (\Exception $e) {
                $this->messageManager->addException($e, __('Something went wrong while saving the testimonial.'));
            }
        }
        return $resultRedirect->setUrl($this->_redirect->getRefererUrl());
    }

    /**
     * @param array $post Post data from contact form
     * @return void
     */
    private function sendEmail($post)
    {
        $this->mail->send($post);
    }
}
