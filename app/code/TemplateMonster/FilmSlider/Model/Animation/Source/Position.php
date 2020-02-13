<?php
/**
 *
 * Copyright © 2015 TemplateMonster. All rights reserved.
 * See COPYING.txt for license details.
 *
 */

namespace TemplateMonster\FilmSlider\Model\Animation\Source;

use TemplateMonster\FilmSlider\Model\Animation\AnimationAbstract;

class Position extends AnimationAbstract
{

    const ANIMATION_TYPE = 'data-position';

    public function getValues()
    {
        $arr = [];
        $config = $this->getConfig();
        $result = $config->get(Position::ANIMATION_TYPE);
        if ($result) {
            $arr = $result;
        }
        return $arr;
    }
}
