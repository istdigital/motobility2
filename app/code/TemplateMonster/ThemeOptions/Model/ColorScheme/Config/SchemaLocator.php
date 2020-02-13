<?php

namespace TemplateMonster\ThemeOptions\Model\ColorScheme\Config;

use Magento\Framework\Module\Dir\Reader;
use Magento\Framework\Module\Dir;
use Magento\Framework\Config\SchemaLocatorInterface;

/**
 * Class SchemaLocator.
 */
class SchemaLocator implements SchemaLocatorInterface
{
    /**
     * Path to corresponding XSD file with validation rules for both individual and merged configs.
     *
     * @var string
     */
    private $_schema;

    /**
     * SchemaLocator constructor.
     *
     * @param Reader $moduleReader
     */
    public function __construct(Reader $moduleReader)
    {
        $this->_schema = $moduleReader->getModuleDir(Dir::MODULE_ETC_DIR, 'TemplateMonster_ThemeOptions').'/color_schemes.xsd';
    }

    /**
     * {@inheritdoc}
     */
    public function getSchema()
    {
        return $this->_schema;
    }

    /**
     * {@inheritdoc}
     */
    public function getPerFileSchema()
    {
        return $this->_schema;
    }
}
