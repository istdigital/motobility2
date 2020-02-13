<?php
/**
 * @copyright   Copyright (c) http://www.manadev.com
 * @license     http://www.manadev.com/license  Proprietary License
 */

namespace Manadev\Core\Controller\Session;

use Magento\Framework\App\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\App\Request\Http as Request;
use Magento\Catalog\Model\Session;
use Magento\Framework\Controller\Result\ForwardFactory;
use Magento\Framework\Controller\ResultFactory;
use Psr\Log\LoggerInterface as Logger;

/**
 * @property Request $_request
 */
class Save extends Action\Action
{
    /**
     * @var Session
     */
    protected $session;
    /**
     * @var Logger
     */
    protected $logger;

    public function __construct(Context $context, Session $session, Logger $logger)
    {
        parent::__construct($context);
        $this->session = $session;
        $this->logger = $logger;
    }

    /**
     * Dispatch request
     *
     * @return \Magento\Framework\Controller\ResultInterface|ResponseInterface
     * @throws \Magento\Framework\Exception\NotFoundException
     */
    public function execute() {
        try {
            $post = $this->_request->getPost()->toArray();

            if (empty($post['key'])) {
                return $this->rawResult('500 Invalid Argument', "'key' parameter should not be empty");
            }

            if (empty($post['value'])) {
                return $this->rawResult('500 Invalid Argument', "'value' parameter should not be empty");
            }

            if (($value = json_decode($post['value'], true)) === null) {
                return $this->rawResult('500 Invalid Argument', "'value' parameter should be valid JSON");
            }

            if (!($values = $this->session->getData('mana'))) {
                $values = [];
            }

            $values[$post['key']] = $value;
            $this->session->setData('mana', $values);

            return $this->rawResult('200 OK');
        }
        catch (\Exception $e) {
            $this->logger->critical($e);
            return $this->rawResult('500 Error', "Error occurred, see exception.log for details.");
        }
    }

    protected function rawResult($status, $message = '') {
        /** @var \Magento\Framework\Controller\Result\Raw $result */
        $result = $this->resultFactory->create(ResultFactory::TYPE_RAW);
        $result->setHeader('Status', $status);
        $result->setContents($message);
        return $result;
    }
}