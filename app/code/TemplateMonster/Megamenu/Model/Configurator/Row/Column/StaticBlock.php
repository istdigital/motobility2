<?php
namespace TemplateMonster\Megamenu\Model\Configurator\Row\Column;

class StaticBlock extends Entity
{
    public $rendererClass = 'StaticBlock';

    public function __construct(
        array $data = []
    ) {
        parent::__construct($data);
    }

}