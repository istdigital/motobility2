<?php

namespace TemplateMonster\SampleDataInstaller\Controller\Adminhtml\Import;

use TemplateMonster\SampleDataInstaller\Model\Import\ImportFactory;
use Magento\Backend\App\Action;
use Magento\Framework\Controller\ResultFactory;

/**
 * Class Index
 */
class Index extends Action
{
    /**
     * @var ImportFactory
     */
    protected $_importFactory;

    /**
     * Index constructor.
     *
     * @param ImportFactory  $importFactory
     * @param Action\Context $context
     */
    public function __construct(
        ImportFactory $importFactory,
        Action\Context $context
    )
    {
        $this->_importFactory = $importFactory;
        parent::__construct($context);
    }

    /**
     * @inheritdoc
     */
    public function execute()
    {
        $type = $this->_getType();
        $params = $this->_getParams();

        try {
            $import = $this->_importFactory->create($type, $params);
            list($imported, $skipped) = $import->import();

            $this->messageManager->addSuccessMessage(__('Import has been successfully completed.'));
            $this->messageManager->addNoticeMessage(__('%1 entities were imported, %2 skipped.', $imported, $skipped));
        }
        catch (\Exception $e) {
            $this->messageManager->addErrorMessage(__('There is an error occurred during import.'));
        }

        /** @var \Magento\Framework\Controller\Result\Redirect $resultRedirect */
        $resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);
        $resultRedirect->setRefererUrl();

        return $resultRedirect;
    }

    /**
     * Get action.
     *
     * @return string
     */
    protected function _getType()
    {
        return $this->getRequest()->getParam('type');
    }

    /**
     * Get params.
     *
     * @return array
     */
    protected function _getParams()
    {
        $allowed = ['website', 'store', 'import_files', 'is_override'];

        $params = [];
        foreach ($this->getRequest()->getParams() as $name => $value) {
            if (in_array($name, $allowed)) {
                $params[$name] = $value;
            }
        }

        return $params;
    }
}