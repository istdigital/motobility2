<?php
/**
 * @copyright   Copyright (c) http://www.manadev.com
 * @license     http://www.manadev.com/license  Proprietary License
 */
namespace Manadev\Core\Controller\Adminhtml\ExtensionControl;

use Magento\Backend\App\AbstractAction;
use Magento\Backend\App\Action\Context;
use Manadev\Core\Features;
use Manadev\Core\Resources\ExtensionCollectionFactory;

class UpdateVersion extends AbstractAction
{
    /**
     * @var Features
     */
    protected $features;
    /**
     * @var ExtensionCollectionFactory
     */
    protected $collectionFactory;

    public function __construct(Context $context, Features $features, ExtensionCollectionFactory $collectionFactory) {
        parent::__construct($context);
        $this->features = $features;
        $this->collectionFactory = $collectionFactory;
    }

    public function execute()
    {
        try {
            $versions = [];
            $collection = $this->collectionFactory->create();
            $collection->setStore(0);
            foreach ($collection as $extension) {
                if (!$extension->getData('version') || !$extension->getData('code')) {
                    continue;
                }
                $versions[$extension->getData('code')] = $extension->getData('version');
            }

            $availableVersions = $this->features->updateVersionInfo($versions);

            $newVersionAvailable = false;
            foreach ($versions as $code => $version) {
                if (!isset($availableVersions[$code])) {
                    continue;
                }

                if (version_compare($version, $availableVersions[$code]) < 0) {
                    $newVersionAvailable = true;
                    break;
                }
            }

            if ($newVersionAvailable) {
                $message = __("New extension versions are available, please check the table below");
            } else {
                $message = __("All extensions are up-to-date");
            }
            $this->messageManager->addSuccess($message);
        } catch(\Exception $e) {
            $this->messageManager->addError(__("MANAdev version server is not available, please try again later."));
        }
        return $this->_redirect('*/*/index', ['store' => $this->getRequest()->getParam('store', 0)]);
    }
}
