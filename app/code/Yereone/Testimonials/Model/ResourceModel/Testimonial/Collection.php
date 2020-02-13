<?php

namespace Yereone\Testimonials\Model\ResourceModel\Testimonial;

use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;

class Collection extends AbstractCollection
{
    /**
     * Identifier field name for collection items
     *
     * Can be used by collections with items without defined
     *
     * @var string
     */
    protected $_idFieldName = 'id'; 

    /**
     * Define resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('Yereone\Testimonials\Model\Testimonial', 'Yereone\Testimonials\Model\ResourceModel\Testimonial');
    }

    /**
     * Define resource model
     *
     * @return string
     */
    public function getIdFieldName()
    {
        return $this->_idFieldName;
    }
}
