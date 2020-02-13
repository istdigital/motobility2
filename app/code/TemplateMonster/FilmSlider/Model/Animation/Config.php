<?php

/**
 *
 * Copyright © 2015 TemplateMonster. All rights reserved.
 * See COPYING.txt for license details.
 *
 */

namespace TemplateMonster\FilmSlider\Model\Animation;

use Magento\Framework\Config\Data as ConfigData;
use Magento\Framework\DataObject;
use TemplateMonster\FilmSlider\Api\Animation\ConfigInterface;

class Config extends ConfigData implements ConfigInterface
{
    public function __construct(
        \TemplateMonster\FilmSlider\Model\Animation\Config\Reader $reader,
        \Magento\Framework\Config\CacheInterface $cache,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        $cacheId = 'animation_format'
    ) {
        parent::__construct($reader, $cache, $cacheId);
    }
}
