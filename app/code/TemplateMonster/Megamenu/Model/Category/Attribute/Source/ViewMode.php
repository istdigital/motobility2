<?php
namespace TemplateMonster\Megamenu\Model\Category\Attribute\Source;

class ViewMode extends \Magento\Eav\Model\Entity\Attribute\Source\AbstractSource
{
    public function getAllOptions()
    {
        if (!$this->_options) {
            $this->_options = [
                ['value' => 'static', 'label' => __('Static')],
                ['value' => 'easing', 'label' => __('Easing')],
                ['value' => 'pop-up', 'label' => __('Pop Up')],
            ];
        }
        return $this->_options;
    }
}
