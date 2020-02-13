<?php

namespace TemplateMonster\SocialSharing\Model\Config\Source;

use Magento\GoogleAdwords\Model\Config\Source\Language;

/**
 * MenuLanguage source model.
 */
class MenuLanguage extends Language
{
    /**
     * {@inheritdoc}
     */
    public function toOptionArray()
    {
        return array_merge(
            [
                ['value' => '', 'label' => 'Auto detect'],
            ],
            parent::toOptionArray()
        );
    }
}
