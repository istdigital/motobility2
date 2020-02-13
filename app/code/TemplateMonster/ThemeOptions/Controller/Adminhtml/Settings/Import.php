<?php

namespace TemplateMonster\ThemeOptions\Controller\Adminhtml\Settings;

use TemplateMonster\ThemeOptions\Controller\Adminhtml\Settings;
use Magento\ImportExport\Model\ImportFactory;
use Magento\Framework\Filesystem;
use Magento\ImportExport\Model\Import\Adapter as ImportAdapter;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Backend\App\Action\Context;

/**
 * Import settings action.
 *
 * @package TemplateMonster\ThemeOptions\Controller\Adminhtml\Settings
 */
class Import extends Settings
{
    /**
     * @var array
     */
    protected $_settings = [
        'entity' => 'theme_options',
        'behavior' => 'replace',
        'validation_strategy' => 'validation-stop-on-errors',
        'allowed_error_count' => 0,
        '_import_field_separator' => ',',
        '_import_multiple_value_separator' => ',',
        'import_images_file_dir' => '',
    ];

    /**
     * @var ImportFactory
     */
    protected $_importFactory;

    /**
     * @var Filesystem
     */
    protected $_filesystem;

    /**
     * Import constructor.
     *
     * @param ImportFactory   $importFactory
     * @param Filesystem      $filesystem
     * @param Context         $context
     */
    public function __construct(
        ImportFactory $importFactory,
        Filesystem $filesystem,
        Context $context
    )
    {
        $this->_importFactory = $importFactory;
        $this->_filesystem = $filesystem;
        parent::__construct($context);
    }

    /**
     * @return \Magento\Framework\Controller\Result\Redirect
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function execute()
    {
        /** @var $import \Magento\ImportExport\Model\Import */
        $import = $this->_importFactory->create();
        $import->setData($this->_settings);

        try {
            $source = ImportAdapter::findAdapterFor(
                $import->uploadSource(),
                $this->_filesystem->getDirectoryWrite(DirectoryList::ROOT),
                $this->_settings[$import::FIELD_FIELD_SEPARATOR]
            );
            if ($import->validateSource($source)) {
                $import->importSource();
                $this->messageManager->addSuccess(__('Settings have been successfully imported.'));
            }
            else {
                $this->messageManager->addError(
                    __('Invalid format of the import file. Please make sure that you are using the correct file.')
                );
            }
        }
        catch (\Exception $e) {
            $this->messageManager->addError($e->getMessage());
        }

        $resultRedirect = $this->resultRedirectFactory->create();
        $resultRedirect->setRefererUrl();

        return $resultRedirect;
    }

    /**
     * @inheritdoc
     */
    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('TemplateMonster_ThemeOptions::theme_options_import');
    }
}