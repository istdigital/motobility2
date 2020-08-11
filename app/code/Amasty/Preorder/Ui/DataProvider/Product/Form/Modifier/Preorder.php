<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2020 Amasty (https://www.amasty.com)
 * @package Amasty_Preorder
 */


namespace Amasty\Preorder\Ui\DataProvider\Product\Form\Modifier;

use Magento\Catalog\Ui\DataProvider\Product\Form\Modifier\AbstractModifier;
use Magento\Catalog\Model\Locator\LocatorInterface;

class Preorder extends AbstractModifier
{
    /**
     * @var LocatorInterface
     */
    protected $locator;

    /**
     * @param LocatorInterface $locator
     */
    public function __construct(
        LocatorInterface $locator
    ) {
        $this->locator = $locator;
    }

    /**
     * @param array $data
     *
     * @return array
     */
    public function modifyData(array $data)
    {
        $model = $this->locator->getProduct();
        $modelId = $model->getId();
        $data[$modelId][self::DATA_SOURCE_DEFAULT]['amasty_preorder_note'] = $model->getAmastyPreorderNote();
        $data[$modelId][self::DATA_SOURCE_DEFAULT]['amasty_preorder_cart_label'] = $model->getAmastyPreorderCartLabel();

        return $data;
    }

    /**
     * @param array $meta
     *
     * @return array
     */
    public function modifyMeta(array $meta)
    {
        return $meta;
    }
}
