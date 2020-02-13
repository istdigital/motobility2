<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Zemez\StockAlert\Model\Source;

use Magento\Framework\Data\OptionSourceInterface;
use Magento\Framework\View\Model\PageLayout\Config\BuilderInterface;

/**
 * Class PageLayout
 */
class SubscriberStatus implements OptionSourceInterface
{
    /**
     * @inheritdoc
     */
    public function toOptionArray()
    {
        $options = [
            ['label' => "To be Notified",'value' => 'to_be_notified'],
            ['label' => "Notified",'value' => 'notified'],
            ['label' => "Followed up",'value' => 'followed_up']
        ];

        return $options;
    }
}
