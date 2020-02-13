<?php
/**
 * @copyright   Copyright (c) http://www.manadev.com
 * @license     http://www.manadev.com/license  Proprietary License
 */
namespace Manadev\Core\Plugins;

use Closure;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Filesystem;
use Magento\Framework\View\Design\Fallback\RulePool;
use Magento\Framework\View\Design\FileResolution\Fallback\Resolver\Simple;
use Magento\Framework\View\Design\ThemeInterface;

class ThemeFileResolver
{
    protected $rootDirectory;

    public function __construct(Filesystem $filesystem) {
        $this->rootDirectory = $filesystem->getDirectoryRead(DirectoryList::ROOT);
    }

    public function aroundResolve(
        Simple $subject,
        Closure $proceed,
        $type, $file, $area = null, ThemeInterface $theme = null, $locale = null, $module = null
    ){
        $result = $proceed($type, $file, $area, $theme, $locale, $module);

        // Only works for MANAdev modules
        if(strpos($module, "Manadev") !== 0) {
            return $result;
        }

        $moduleDir = str_replace("_", "/", $module);
        $relativePath = $this->rootDirectory->getRelativePath($result);
        if (strpos($relativePath, "app/code/{$moduleDir}/") !== 0 &&
            strpos($relativePath, "manadev-products/code/{$moduleDir}/") !== 0 )
        {
            return $result;
        }

        $tmpTheme = $theme;
        while($tmpTheme) {
            // If the theme is based from Magento_luma, take resource from MANAdev module.
            if($tmpTheme->getCode() == "Magento/luma") {
                $path = $this->rootDirectory->getAbsolutePath("app/code/{$moduleDir}/view-Magento_luma/{$area}/");
                switch($type) {
                    case RulePool::TYPE_TEMPLATE_FILE:
                        $path .= "templates/";
                        break;
                    case RulePool::TYPE_STATIC_FILE:
                        $path .= "web/";
                        break;
                }
                $path .= $file;
                if($this->rootDirectory->isExist($this->rootDirectory->getRelativePath($path))) {
                    return $path;
                }
                break;
            }
            $tmpTheme = $tmpTheme->getParentTheme();
        }

        return $result;
    }
}