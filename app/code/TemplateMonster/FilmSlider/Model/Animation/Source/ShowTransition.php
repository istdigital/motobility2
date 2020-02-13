<?php
/**
 *
 * Copyright © 2015 TemplateMonster. All rights reserved.
 * See COPYING.txt for license details.
 *
 */

namespace TemplateMonster\FilmSlider\Model\Animation\Source;

use TemplateMonster\FilmSlider\Model\Animation\AnimationAbstract;

class ShowTransition extends AnimationAbstract
{

    const ANIMATION_TYPE = 'data-show-transition';

    public function getValues()
    {
        $arr = [];
        $config = $this->getConfig();
        $result = $config->get(ShowTransition::ANIMATION_TYPE);
        if ($result) {
            $arr = $result;
        }
        return $arr;
    }
}
