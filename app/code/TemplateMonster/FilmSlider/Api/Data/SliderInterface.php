<?php

/**
 *
 * Copyright © 2015 TemplateMonster. All rights reserved.
 * See COPYING.txt for license details.
 *
 */

namespace TemplateMonster\FilmSlider\Api\Data;

interface SliderInterface
{

    /**
     * Constants defined for keys of  data array
     */

    const REGISTRY_NAME = 'film_slider';

    const NAME = 'name';

    const STATUS = 'status';

    const PARAMS = 'params';

    const PARAMS_ARRAY = 'params_array';

    const STORE = 'stores';

    public function getId();

    public function setId($id);

    public function getStatus();

    public function setStatus($status);

    public function getParams();

    public function setParams($params);
}
