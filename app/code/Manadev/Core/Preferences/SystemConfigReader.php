<?php
/**
 * @copyright   Copyright (c) http://www.manadev.com
 * @license     http://www.manadev.com/license  Proprietary License
 */
namespace Manadev\Core\Preferences;

use Magento\Config\Model\Config\Structure\Converter;
use Magento\Framework\Config\FileIterator;
use Magento\Framework\View\TemplateEngine\Xhtml\CompilerInterface;
use Manadev\Core\Features;

class SystemConfigReader extends \Magento\Config\Model\Config\Structure\Reader {
    /**
     * @var Features
     */
    protected $features;

    public function __construct(
        \Magento\Framework\Config\FileResolverInterface $fileResolver,
        Converter $converter,
        \Magento\Config\Model\Config\SchemaLocator $schemaLocator,
        \Magento\Framework\Config\ValidationStateInterface $validationState,
        CompilerInterface $compiler,
        Features $features,
        $fileName = 'system.xml',
        $idAttributes = [],
        $domDocumentClass = 'Magento\Framework\Config\Dom',
        $defaultScope = 'global'
    ) {
        parent::__construct($fileResolver, $converter, $schemaLocator, $validationState, $compiler, $fileName, $idAttributes, $domDocumentClass, $defaultScope);
        $this->features = $features;
    }

    protected function _readFiles($fileList) {
        if($fileList instanceof FileIterator) {
            $fileList = $fileList->toArray();
        }
        foreach(array_keys($fileList) as $file) {
            $parts = explode("/",$file);
            $module = implode("_", [$parts[count($parts) - 5], $parts[count($parts) - 4]]);
            if(!$this->features->isEnabled($module, 0)) {
                unset($fileList[$file]);
            }
        }

        return parent::_readFiles($fileList);
    }
}