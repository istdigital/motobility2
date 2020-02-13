<?php
namespace Zemez\StockAlert\Model;

use Magento\Framework\DataObject\IdentityInterface;
use Magento\Framework\Model\AbstractModel;


class Subscriber extends AbstractModel implements IdentityInterface
{
    const CACHE_TAG = 'zstock_a';

    protected $_cacheTag = self::CACHE_TAG;

    protected $_eventPrefix = 'stock_alert';

    /**
     * @return void
     */
    protected function _construct()
    {
        $this->_init(\Zemez\StockAlert\Model\ResourceModel\Subscriber::class);
    }

    public function getIdentities()
    {
        return [self::CACHE_TAG . '_' . $this->getId()];
    }
}
