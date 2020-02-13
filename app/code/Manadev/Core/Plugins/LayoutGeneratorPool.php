<?php
/**
 * @copyright   Copyright (c) http://www.manadev.com
 * @license     http://www.manadev.com/license  Proprietary License
 */

namespace Manadev\Core\Plugins;

use Magento\Framework\View\Layout\GeneratorPool as OriginalClass;
use Magento\Framework\View\Layout\ScheduledStructure;
use Manadev\Core\Blocks\Instruction;
use Manadev\Core\Features;

use Magento\Framework\View\Layout\Reader;
use Magento\Framework\View\Layout\Generator;

class LayoutGeneratorPool
{
    /**
     * @var Features
     */
    protected $features;

    public function __construct(Features $features) {

        $this->features = $features;
    }

    /**
     * @param OriginalClass $object
     * @param Reader\Context $readerContext
     * @param Generator\Context $generatorContext
     * @return mixed
     */
    public function beforeProcess(OriginalClass $object, Reader\Context $readerContext, Generator\Context $generatorContext) {
        $structure = $readerContext->getScheduledStructure();
        foreach (array_keys($structure->getStructure()) as $name) {
            $data = $structure->getStructureElementData($name);

            if (!isset($data['attributes']) || !isset($data['attributes']['class']) || !isset($data['actions'])) {
                continue;
            }

            if ($data['attributes']['class'] != Instruction::class) {
                continue;
            }

            foreach ($data['actions'] as $action) {
                list($actionName, $actionParame) = $action;
                switch ($actionName) {
                    case 'remove':
                        $this->remove($structure, $actionParame);
                        break;
                }
            }
        }
        return null;
    }

    /**
     * @param ScheduledStructure $structure
     * @param $params
     */
    protected function remove($structure, $params) {
        if (!isset($params['blockToBeRemoved']) || !isset($params['removedByFeature'])) {
            return;
        }
        $blockToBeRemoved = $params['blockToBeRemoved']['value'];
        $removedByFeature = $params['removedByFeature']['value'];

        if (!$this->features->isEnabled($removedByFeature)) {
            return;
        }

        $structure->setElementToRemoveList($blockToBeRemoved);
    }
}