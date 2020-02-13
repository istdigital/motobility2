<?php
/**
 * @copyright   Copyright (c) http://www.manadev.com
 * @license     http://www.manadev.com/license  Proprietary License
 */
namespace Manadev\Core\Plugins;

use Closure;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Filesystem;
use Magento\Framework\View\Element\Template\File\Validator;

class TemplateFileValidator
{
    protected $rootDirectory;

    public function __construct(Filesystem $filesystem) {
        $this->rootDirectory = $filesystem->getDirectoryRead(DirectoryList::ROOT);
    }

    public function aroundIsValid(Validator $subject, Closure $proceed, $filename){
        if(strpos($filename, "view-Magento_luma") !== false) {
            return $this->rootDirectory->isFile($this->rootDirectory->getRelativePath($filename));
        }
        return $proceed($filename);
    }
}