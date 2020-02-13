<?php

/**
 *
 * Copyright © 2015 TemplateMonster. All rights reserved.
 * See COPYING.txt for license details.
 *
 */

namespace TemplateMonster\FilmSlider\Model\Animation\Source;

use TemplateMonster\FilmSlider\Model\Animation\AnimationAbstract;

class LayerKlass extends AnimationAbstract
{

    const ANIMATION_TYPE = 'layer-classes';

    public function getValues()
    {
        $arr = [];
        $config = $this->getConfig();
        $result = $config->get(LayerKlass::ANIMATION_TYPE);
        if ($result) {
            $arr = $result;
        }
        return $arr;
    }
}
