<?php
namespace Zemez\Contact\Model;

use Magento\Framework\DataObject\IdentityInterface;
use Magento\Framework\Model\AbstractModel;


class Contact extends AbstractModel implements IdentityInterface
{
    const CACHE_TAG = 'zcontact';

    protected $_cacheTag = self::CACHE_TAG;

    protected $_eventPrefix = 'zcontact';

    /**
     * @return void
     */
    protected function _construct()
    {
        $this->_init(\Zemez\Contact\Model\ResourceModel\Contact::class);
    }

    public function getIdentities()
    {
        return [self::CACHE_TAG . '_' . $this->getId()];
    }
}
