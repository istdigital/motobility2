<?php

namespace Yereone\Testimonials\Model;

use Magento\Framework\Model\AbstractModel;

class Testimonial extends AbstractModel
{
    /**#@+
     * Testimonial's Statuses
     */
    const STATUS_APPROVED = 1;
    const STATUS_PENDING = 2;
    const STATUS_NOT_APPROVED = 3;
    /**#@-*/

    /**
     * Initialize resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('Yereone\Testimonials\Model\ResourceModel\Testimonial');
    }

    /**
     * Prepare testimonial's statuses.
     *
     * @return array
     */
    public function getAvailableStatuses()
    {
        return [
            self::STATUS_APPROVED => __('Approved'),
            self::STATUS_PENDING => __('Pending'),
            self::STATUS_NOT_APPROVED => __('Not Approved')
        ];
    }
}
