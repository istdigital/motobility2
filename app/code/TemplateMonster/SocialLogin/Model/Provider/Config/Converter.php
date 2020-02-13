<?php

namespace TemplateMonster\SocialLogin\Model\Provider\Config;

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
        /** @var \DOMNode $templateNode */
        foreach ($source->documentElement->childNodes as $templateNode) {
            if ($templateNode->nodeType == XML_ELEMENT_NODE) {
                $providerId = $templateNode->attributes->getNamedItem('id')->nodeValue;
                $providerLabel = $templateNode->attributes->getNamedItem('label')->nodeValue;
                $providerClass = $templateNode->attributes->getNamedItem('class')->nodeValue;

                $result[$providerId] = [
                    'label' => $providerLabel,
                    'class' => $providerClass,
                ];
            }
        }

        return $result;
    }
}
