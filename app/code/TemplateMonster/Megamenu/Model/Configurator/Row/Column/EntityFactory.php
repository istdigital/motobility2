<?php

namespace TemplateMonster\Megamenu\Model\Configurator\Row\Column;

use Magento\Framework\ObjectManagerInterface;

class EntityFactory
{
    /**
     * @var ObjectManagerInterface
     */
    protected $_objectManager;

    /**
     * @param ObjectManagerInterface $objectManager
     */
    public function __construct(ObjectManagerInterface $objectManager)
    {
        $this->_objectManager = $objectManager;
    }

    public function create(array $entityData = [])
    {
        $className = 'TemplateMonster\Megamenu\Model\Configurator\Row\Column';
        $value = $entityData['value'];
        switch (substr($value, 0, 5)) {
            case ('produ') :
                $className .= '\Products';
                $value = substr($value, 9, strlen($value) - 9);
                break;
            case ('block') :
                $className .= '\StaticBlock';
                $value = substr($value, 6, strlen($value) - 6);
                break;
            case ('subca') :
                $className .= '\Category';
                $value = substr($value, 7, strlen($value) - 7);
                break;
            case ('widge') :
                $className .= '\Widget';
                $value = substr($value, 7, strlen($value) - 7);
                break;
            case ('video') :
                $className .= '\Video';
                $value = substr($value, 6, strlen($value) - 6);
                break;
        }
        $element = $this->_objectManager->create($className);
        $element->setValue($value);
        return $element;
    }
}