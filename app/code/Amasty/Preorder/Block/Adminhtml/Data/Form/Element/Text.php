<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2020 Amasty (https://www.amasty.com)
 * @package Amasty_Preorder
 */


declare(strict_types=1);

namespace Amasty\Preorder\Block\Adminhtml\Data\Form\Element;

class Text extends \Magento\Framework\Data\Form\Element\Text
{
    /**
     * @return string
     */
    public function getAfterElementHtml()
    {
        $elementId = $this->getId();
        $dataAttribute = "data-disable='{$elementId}'";
        $dataCheckboxName = "toggle_{$elementId}";
        $checkboxLabel = __('Change');
        $html = <<<HTML
<span class="attribute-change-checkbox">
    <input type="checkbox" id="$dataCheckboxName" name="$dataCheckboxName" class="checkbox"
    onclick="toogleFieldEditMode(this, '{$elementId}')" $dataAttribute />
    <label class="label" for="$dataCheckboxName">
        {$checkboxLabel}
    </label>
</span>
HTML;

        return $html;
    }
}
