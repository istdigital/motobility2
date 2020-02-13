<?php
/**
 *
 * Copyright © 2015 TemplateMonster. All rights reserved.
 * See COPYING.txt for license details.
 *
 */

namespace TemplateMonster\FilmSlider\Model\Animation\Source;

use TemplateMonster\FilmSlider\Model\Animation\AnimationAbstract;

class HideTransition extends AnimationAbstract
{

    const ANIMATION_TYPE = 'data-hide-transition';

    public function getValues()
    {
        $arr = [];
        $config = $this->getConfig();
        $result = $config->get(HideTransition::ANIMATION_TYPE);
        if ($result) {
            $arr = $result;
        }
        return $arr;
    }
}
