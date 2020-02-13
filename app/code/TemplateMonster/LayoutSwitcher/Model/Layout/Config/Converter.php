<?php

namespace TemplateMonster\LayoutSwitcher\Model\Layout\Config;

use Magento\Framework\Config\ConverterInterface;

/**
 * Class Converter.
 */
class Converter implements ConverterInterface
{
    /**
     * {@inheritdoc}
     */
    public function convert($source)
    {
        $result = [];
        /** @var \DOMNode $layoutNode */
        foreach ($source->documentElement->childNodes as $layoutNode) {
            if ($layoutNode->nodeType != XML_ELEMENT_NODE) {
                continue;
            }

            $id = $layoutNode->attributes->getNamedItem('id')->nodeValue;
            $label = $layoutNode->attributes->getNamedItem('label')->nodeValue;
            $type = $layoutNode->attributes->getNamedItem('type')->nodeValue;
            $node = $layoutNode->attributes->getNamedItem('depends');
            $depends = $node ? $node->nodeValue : null;

            $result[$id] = [
                'label' => $label,
                'type' => $type,
                'depends' => $depends
            ];
        }

        return $result;
    }
}
