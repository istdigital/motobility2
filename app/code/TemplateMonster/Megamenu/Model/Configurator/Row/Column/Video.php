<?php
namespace TemplateMonster\Megamenu\Model\Configurator\Row\Column;

class Video extends Entity
{
    public $rendererClass = 'Video';

    public function __construct(
        array $data = []
    ) {
        parent::__construct($data);
    }

}