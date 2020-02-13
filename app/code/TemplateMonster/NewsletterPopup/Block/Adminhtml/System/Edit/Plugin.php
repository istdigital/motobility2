<?php

namespace TemplateMonster\NewsletterPopup\Block\Adminhtml\System\Edit;

use Magento\Config\Block\System\Config\Edit;

/**
 * Config edit plugin.
 *
 * @package TemplateMonster\NewsletterPopup\Block\Adminhtml\System\Edit
 */
class Plugin
{
    /**
     * Config sections
     *
     * @var array
     */
    protected $sections;

    /**
     * Plugin constructor.
     *
     * @param array $sections
     */
    public function __construct(array $sections)
    {
        $this->sections = $sections;
    }

    /**
     * After set layout.
     *
     * @param Edit $subject
     */
    public function afterSetLayout(Edit $subject)
    {
        $section = $subject->getRequest()->getParam('section');

        if (in_array($section, $this->sections)) {
            $subject->getToolbar()->addChild(
                'reset_button',
                'Magento\Backend\Block\Widget\Button',
                [
                    'id' => 'reset',
                    'label' => __('Reset'),
                    'class' => 'reset',
                    'onclick' => sprintf(
                        'confirm("%s") && (window.location="%s")',
                        __('Are you sure you want to reset settings?'),
                        $subject->getUrl('newsletter_popup/settings/reset', ['section' => $section])
                    ),
                ]
            );
        }
    }
}