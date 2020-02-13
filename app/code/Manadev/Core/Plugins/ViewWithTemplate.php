<?php
/**
 * @copyright   Copyright (c) http://www.manadev.com
 * @license     http://www.manadev.com/license  Proprietary License
 */

namespace Manadev\Core\Plugins;

use Magento\Backend\Block\Widget\Grid;
use Manadev\Core\Features;
use Magento\Framework\View\Element\Template as OriginalClass;

class ViewWithTemplate
{
    /**
     * @var Features
     */
    protected $features;

    public function __construct(Features $features) {
        $this->features = $features;
    }

    public function aroundFetchView(OriginalClass $object, $proceed, ...$args) {
        if (($moduleName = $this->getModuleName($object)) && !$this->features->isEnabled($moduleName)) {
            return '';
        }

        if ($object instanceof Grid) {
            $this->removeDisabledColumns($object);
        }

        return $proceed(...$args);
    }

    protected function getModuleName(OriginalClass $object) {
        if (($pos = strpos($object->getTemplate(), '::')) !== false) {
            return substr($object->getTemplate(), 0, $pos);
        }

        if ($moduleName = $object->getModuleName()) {
            return $moduleName;
        }

        return false;
    }

    protected function removeDisabledColumns(Grid $grid) {
        if (!($columnSet = $grid->getColumnSet())) {
            return;
        }

        foreach ($columnSet->getColumns() as $columnBlockName => $column) {
            if (! ($moduleName = $column->getData('module_name'))) {
                continue;
            }

            if (!$this->features->isEnabled($moduleName, 0)) {
                $columnSet->getLayout()->unsetChild($columnSet->getNameInLayout(), $columnBlockName);
            }
        }
    }
}