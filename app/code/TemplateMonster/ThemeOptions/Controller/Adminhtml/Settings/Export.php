<?php

namespace TemplateMonster\ThemeOptions\Controller\Adminhtml\Settings;

use TemplateMonster\ThemeOptions\Model\Export\SettingsFactory;
use TemplateMonster\ThemeOptions\Controller\Adminhtml\Settings;
use Magento\Framework\App\Response\Http\FileFactory;
use Magento\ImportExport\Model\Export\Adapter\AbstractAdapter;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Backend\App\Action\Context;

/**
 * Export settings action
 *
 * @package TemplateMonster\ThemeOptions\Controller\Adminhtml\Settings
 */
class Export extends Settings
{
    /**
     * @var SettingsFactory
     */
    protected $_settingsFactory;

    /**
     * @var FileFactory
     */
    protected $_fileFactory;

    /**
     * @var AbstractAdapter
     */
    protected $_writer;

    /**
     * Export file name.
     */
    const FILENAME = 'theme_options.csv';

    /**
     * Export constructor.
     *
     * @param Context         $context
     * @param SettingsFactory $settingsFactory
     * @param FileFactory     $fileFactory
     * @param AbstractAdapter $writer
     */
    public function __construct(
        Context $context,
        SettingsFactory $settingsFactory,
        FileFactory $fileFactory,
        AbstractAdapter $writer
    )
    {
        $this->_settingsFactory = $settingsFactory;
        $this->_fileFactory = $fileFactory;
        $this->_writer = $writer;
        parent::__construct($context);
    }

    /**
     * @inheritdoc
     */
    public function execute()
    {
        $settings = $this->_settingsFactory->create();
        $settings->setWriter($this->_writer);

        return $this->_fileFactory->create(
            self::FILENAME,
            $settings->export(),
            DirectoryList::VAR_DIR
        );
    }

    /**
     * @inheritdoc
     */
    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('TemplateMonster_ThemeOptions::theme_options_export');
    }
}