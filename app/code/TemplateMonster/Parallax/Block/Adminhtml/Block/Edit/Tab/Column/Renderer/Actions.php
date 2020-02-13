<?php

namespace TemplateMonster\Parallax\Block\Adminhtml\Block\Edit\Tab\Column\Renderer;

use Magento\Backend\Block\Widget\Grid\Column\Renderer\Action;
use Magento\Framework\DataObject;

/**
 * Class Actions
 *
 * @package TemplateMonster\Parallax\Block\Adminhtml\Block\Edit\Tab\Column\Renderer
 */
class Actions extends Action
{
    /**
     * @inheritdoc
     */
    public function render(DataObject $row)
    {
        $this->getColumn()->setActions(
            [
                [
                    'url' => $this->getUrl('parallax/blockitem/edit', ['item_id' => $row->getId()]),
                    'caption' => __('Edit'),
                ],
            ]
        );

        return parent::render($row);
    }
}
