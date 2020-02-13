<?php
/**
 *
 * Copyright © 2015 TemplateMonster. All rights reserved.
 * See COPYING.txt for license details.
 *
 */

namespace TemplateMonster\FilmSlider\Model\Animation\Config;

class Converter implements \Magento\Framework\Config\ConverterInterface
{
    /**
     * Convert customer address format configuration from dom node tree to array
     *
     * @param \DOMDocument $source
     * @return array
     */
    public function convert($source)
    {
        $output = [];
        /** @var \DOMNodeList $formats */
        $animations = $source->getElementsByTagName('animation');
        /** @var \DOMNode $formatConfig */

        $animationsArray = [];

        foreach ($animations as $animationTypes) {
            $animationCode = $animationTypes->attributes->getNamedItem('code')->nodeValue;
            $animationsArray[$animationCode] = [];
            $animationValues = $animationTypes->getElementsByTagName('value');

            foreach ($animationValues as $key => $types) {
                $valueLabel = $types->attributes->getNamedItem('label')->nodeValue;
                $animationsArray[$animationCode][$key] = ['label'=>$valueLabel];
                $items = $types->getElementsByTagName('item');

                foreach ($items as $item) {
                    $itemValue = $item->attributes->getNamedItem('value')->nodeValue;
                    $itemLabel = $item->attributes->getNamedItem('label')->nodeValue;
                    $animationsArray[$animationCode][$key]['value'][] = [
                        'value'=> $itemValue,
                        'label' => $itemLabel
                    ];
                }
            }
        }
        return $animationsArray;
    }
}
