<?php
namespace Zemez\Repayment\Model;

use Magento\Framework\DataObject\IdentityInterface;
use Magento\Framework\Model\AbstractModel;


class Enquiry extends AbstractModel implements IdentityInterface
{
    const CACHE_TAG = 'repayment_enquiry';

    protected $_cacheTag = self::CACHE_TAG;

    protected $_eventPrefix = 'repayment_enquiry';

    /**
     * @return void
     */
    protected function _construct()
    {
        $this->_init(\Zemez\Repayment\Model\ResourceModel\Enquiry::class);
    }

    public function getIdentities()
    {
        return [self::CACHE_TAG . '_' . $this->getId()];
    }
}