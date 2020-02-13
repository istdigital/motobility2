<?php

namespace TemplateMonster\ThemeOptions\Model\Import;

use TemplateMonster\ThemeOptions\Model\Export\Settings as ExportModel;
use Magento\Config\Model\Config\Structure as ConfigStructure;
use Magento\Config\Model\ResourceModel\Config\Data as ConfigResource;
use Magento\ImportExport\Model\Import\AbstractEntity;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\Stdlib\StringUtils;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\ImportExport\Model\ImportFactory;
use Magento\ImportExport\Model\ResourceModel\Helper as ResourceHelper;
use Magento\ImportExport\Model\Import\ErrorProcessing\ProcessingErrorAggregatorInterface;

/**
 * Settings import model.
 *
 * @package TemplateMonster\ThemeOptions\Model\Import
 */
class Settings extends AbstractEntity
{
    /**
     * @var array
     */
    protected $_permanentAttributes = ['scope', 'scope_id', 'path', 'value'];

    /***
     * @var string
     */
    protected $masterAttributeCode = 'path';

    /**
     * @var ConfigStructure
     */
    protected $_configStructure;

    /**
     * Not valid config path error code.
     */
    const ERROR_CODE_NOT_VALID_PATH = 'notValidPath';

    /**
     * @var string
     */
    protected $_entityTable;

    public function __construct(
        ConfigStructure $configStructure,
        ConfigResource $configResource,
        StringUtils $string,
        ScopeConfigInterface $scopeConfig,
        ImportFactory $importFactory,
        ResourceHelper $resourceHelper,
        ResourceConnection $resource,
        ProcessingErrorAggregatorInterface $errorAggregator,
        array $data = []
    ) {
        parent::__construct(
            $string,
            $scopeConfig,
            $importFactory,
            $resourceHelper,
            $resource,
            $errorAggregator,
            $data
        );

        $this->addMessageTemplate(self::ERROR_CODE_NOT_VALID_PATH, __('Invalid config parameter.'));
        $this->_configStructure = $configStructure;
        $this->_entityTable = $configResource->getMainTable();
    }

    /**
     * @inheritdoc
     */
    public function getEntityTypeCode()
    {
        return 'theme_options';
    }

    /**
     * @inheritdoc
     */
    public function validateRow(array $rowData, $rowNumber)
    {
        return $this->isValidConfigPath($rowData, $rowNumber);
    }

    /**
     * @inheritdoc
     */
    protected function _importData()
    {
        $this->_connection->beginTransaction();

        try {
            $this->_connection->delete(
                $this->_entityTable,
                ['path LIKE ?' => 'theme_options/%']
            );

            while ($bunch = $this->_dataSourceModel->getNextBunch()) {
                foreach ($bunch as $rowNumber => $rowData) {
                    if (!$this->validateRow($rowData, $rowNumber)) {
                        continue;
                    }
                    if ($this->getErrorAggregator()->hasToBeTerminated()) {
                        $this->getErrorAggregator()->addRowToSkip($rowNumber);
                        continue;
                    }

                    $this->_connection->insert(
                        $this->_entityTable,
                        $rowData
                    );
                }
            }

            $this->_connection->commit();
        }
        catch (\Exception $e) {
            $e->rollBack();

            throw $e;
        }
    }

    /**
     * Check if config path is valid.
     *
     * @param array $rowData
     * @param int   $rowNumber
     *
     * @return bool
     */
    protected function isValidConfigPath(array $rowData, $rowNumber)
    {
        $path = $rowData['path'];

        if (ExportModel::PATH_METADATA == $path) {
            return true;
        }

        $element = $this->_configStructure->getElement($path);
        if (!$element->isVisible()) {
            $this->addRowError(self::ERROR_CODE_NOT_VALID_PATH, $rowNumber);
            return false;
        }

        return true;
    }
}