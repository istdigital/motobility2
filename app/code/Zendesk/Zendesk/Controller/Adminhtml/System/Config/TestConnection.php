<?php

namespace Zendesk\Zendesk\Controller\Adminhtml\System\Config;

use Magento\Backend\App\Action;

class TestConnection extends \Magento\Backend\App\Action
{
    /**
     * ACL resource ID
     *
     * @see _isAllowed()
     */
    const ADMIN_RESOURCE = 'Zendesk_Zendesk::zendesk';
    /**
     * @var \Magento\Framework\Controller\Result\JsonFactory
     */
    protected $resultJsonFactory;
    /**
     * @var \Zendesk\Zendesk\Helper\Api
     */
    protected $apiHelper;
    /**
     * @var \Magento\Framework\Filter\StripTags
     */
    protected $tagFilter;

    /**
     * TestConnection constructor.
     * @param Action\Context $context
     * @param \Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory
     * @param \Zendesk\Zendesk\Helper\Api $apiHelper
     * @param \Magento\Framework\Filter\StripTags $tagFilter
     */
    public function __construct(
        Action\Context $context,
        \Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory,
        \Zendesk\Zendesk\Helper\Api $apiHelper,
        \Magento\Framework\Filter\StripTags $tagFilter
    ) {
        parent::__construct($context);
        $this->resultJsonFactory = $resultJsonFactory;
        $this->apiHelper = $apiHelper;
        $this->tagFilter = $tagFilter;
    }

    /**
     * Check for connection to server
     *
     * @return \Magento\Framework\Controller\Result\Json
     */
    public function execute()
    {
        $result = [
            'success' => false,
            'errorMessage' => '',
        ];

        try {
            $this->apiHelper->tryAuthenticate();

            // Success! :partyparrot:

            $result['success'] = true;
        } catch (\Zendesk\API\Exceptions\AuthException $e) {
            $result['errorMessage'] = $e->getMessage();
        } catch (\Exception $e) {
            $message = __($e->getMessage());
            $result['errorMessage'] = $this->tagFilter->filter($message);
        }

        /** @var \Magento\Framework\Controller\Result\Json $resultJson */
        $resultJson = $this->resultJsonFactory->create();
        return $resultJson->setData($result);
    }
}
