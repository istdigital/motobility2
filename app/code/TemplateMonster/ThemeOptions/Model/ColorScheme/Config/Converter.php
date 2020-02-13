<?php

namespace TemplateMonster\ThemeOptions\Model\ColorScheme\Config;

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
        /** @var \DOMNode $colorSchemeNode */
        foreach ($source->documentElement->childNodes as $storeNode) {
            if ($storeNode->nodeType != XML_ELEMENT_NODE) {
                continue;
            }
            $website = $storeNode->attributes->getNamedItem('code')->nodeValue;

            foreach ($storeNode->childNodes as $colorSchemeNode) {
                if ($colorSchemeNode->nodeType != XML_ELEMENT_NODE) {
                    continue;
                }

                $id = $colorSchemeNode->attributes->getNamedItem('id')->nodeValue;
                $label = $colorSchemeNode->attributes->getNamedItem('label')->nodeValue;
                $color = $colorSchemeNode->attributes->getNamedItem('color')->nodeValue;

                $params = [];
                /** @var \DOMNode $paramNode */
                foreach ($colorSchemeNode->childNodes as $paramNode) {
                    if ($paramNode->nodeType != XML_ELEMENT_NODE) {
                        continue;
                    }

                    $name = $paramNode->attributes->getNamedItem('name')->nodeValue;
                    $value = $paramNode->attributes->getNamedItem('value')->nodeValue;

                    $params[$name] = $value;
                }

                $result[$website][$id] = [
                    'label' => $label,
                    'color' => $color,
                    'params' => $params
                ];
            }
        }

        return $result;
    }
}
