<?php

namespace TemplateMonster\ThemeOptions\Block\Adminhtml\System\Edit;

use Magento\Config\Block\System\Config\Edit;

/**
 * Config edit plugin.
 *
 * @package TemplateMonster\ThemeOptions\Block\Adminhtml\System\Edit
 */
class Plugin
{
    /**
     * Config sections.
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
            $toolbar = $subject->getToolbar();
            $toolbar->addChild(
                'reset_button',
                'Magento\Backend\Block\Widget\Button',
                [
                    'id' => 'reset',
                    'label' => __('Reset'),
                    'class' => 'reset',
                    'style' => 'float:left',
                    'onclick' => $this->getResetHandler(
                        __('Are you sure you want to reset settings?'),
                        $subject->getUrl('theme_options/settings/reset', ['section' => $section])
                    )
                ]
            );
            $toolbar->addChild(
                'export_button',
                'Magento\Backend\Block\Widget\Button',
                [
                    'id' => 'export',
                    'label' => __('Export'),
                    'class' => 'export',
                    'style' => 'float:left',
                    'onclick' => sprintf(
                        'window.location="%s"',
                        $subject->getUrl('theme_options/settings/export')
                    ),
                ]
            );
            $toolbar->addChild(
                'import_button',
                'Magento\Backend\Block\Widget\Button',
                [
                    'id' => 'import',
                    'label' => __('Import'),
                    'class' => 'import',
                    'style' => 'float:left',
                    'data_attribute' => [
                        'mage-init' => [
                            'TemplateMonster_ThemeOptions/js/import-button' => [
                                'importProcessorUrl' => $subject->getUrl('theme_options/settings/import')
                            ]
                        ]
                    ]
                ]
            );
        }
    }

    /**
     * Get reset button handler.
     *
     * @param string $text
     * @param string $url
     *
     * @return string
     */
    protected function getResetHandler($text, $url)
    {
        return <<<EOL
            jQuery("<span>$text</span>").confirm({
                title: "Confirmation",
                actions: {
                    confirm: function() {
                        window.location = "$url";
                    }
                }
            });
EOL;
    }
}