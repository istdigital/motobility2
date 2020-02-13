<?php
/**
 *
 * Copyright © 2015 TemplateMonster. All rights reserved.
 * See COPYING.txt for license details.
 *
 */

namespace TemplateMonster\FilmSlider\Model\Animation;

abstract class AnimationAbstract
{

    protected $_animationConfig;

    public function __construct(\TemplateMonster\FilmSlider\Api\Animation\ConfigInterface $animationConfig)
    {
        $this->_animationConfig = $animationConfig;
    }

    protected function getConfig()
    {
        return $this->_animationConfig;
    }

    abstract public function getValues();
}
