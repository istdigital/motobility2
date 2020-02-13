<?php
/**
 * @copyright   Copyright (c) http://www.manadev.com
 * @license     http://www.manadev.com/license  Proprietary License
 */

namespace Manadev\LayeredNavigation\Resources;

use Magento\Framework\Model\ResourceModel\Db;
use Magento\Store\Model\StoreManagerInterface;
use Manadev\Core\Configuration;

class HomePageResource extends Db\AbstractDb
{
    /**
     * @var StoreManagerInterface
     */
    protected $storeManager;
    /**
     * @var Configuration
     */
    protected $configuration;

    public function __construct(Db\Context $context, StoreManagerInterface $storeManager, Configuration $configuration,
        $connectionName = null)
    {
        parent::__construct($context, $connectionName);
        $this->storeManager = $storeManager;
        $this->configuration = $configuration;
    }

    public function getSelect($storeId, $identifier) {
        $db = $this->getConnection();

        return $db->select()->from(['p' => $this->getMainTable()], 'mana_add_layered_navigation_and_products')
            ->join(['s' => $this->getTable('cms_page_store')],
                $db->quoteInto("`s`.`page_id` = `p`.`page_id` AND `s`.`store_id` IN (?)", [0, $storeId]), null)
            ->where("`p`.`identifier` = ?", $identifier)
            ->where("`p`.`is_active` = 1");
    }

    /**
     * Initialize resource model
     *
     * @return void
     */
    protected function _construct() {
        $this->_init('cms_page', 'page_id');
    }

    public function doesHomePageContainsLayeredNavigation() {
        if ($this->configuration->getHomePageType() != 'cms') {
            return false;
        }

        $storeId = $this->storeManager->getStore()->getId();
        $identifier = $this->configuration->getCmsHomePage();
        $db = $this->getConnection();

        return $db->fetchOne($this->getSelect($storeId, $identifier));
    }
}