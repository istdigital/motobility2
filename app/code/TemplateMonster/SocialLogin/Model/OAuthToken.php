<?php

namespace TemplateMonster\SocialLogin\Model;

use Magento\Framework\Data\Collection\AbstractDb;
use Magento\Framework\Model\ResourceModel\AbstractResource;
use Magento\Framework\Registry;
use Magento\Framework\Model\Context;
use Magento\Framework\Stdlib\DateTime\DateTimeFactory;
use Magento\Framework\Model\AbstractModel;

/**
 * OAuthToken entity model.
 */
class OAuthToken extends AbstractModel
{
    /**
     * @var DateTimeFactory
     */
    protected $_dateFactory;

    /**
     * OAuthToken constructor.
     *
     * @param DateTimeFactory       $dateFactory
     * @param Context               $context
     * @param Registry              $registry
     * @param AbstractResource|null $resource
     * @param AbstractDb|null       $resourceCollection
     * @param array                 $data
     */
    public function __construct(
        DateTimeFactory $dateFactory,
        Context $context,
        Registry $registry,
        AbstractResource $resource = null,
        AbstractDb $resourceCollection = null,
        array $data = []
    ) {
        $this->_dateFactory = $dateFactory;
        parent::__construct(
            $context,
            $registry,
            $resource,
            $resourceCollection,
            $data
        );
    }

    /**
     * {@inheritdoc}
     */
    public function _construct()
    {
        $this->_init('TemplateMonster\SocialLogin\Model\ResourceModel\OAuthToken');
    }

    /**
     * Get entity by provider.
     *
     * @param string $code
     * @param string $id
     *
     * @return $this
     *
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getByProvider($code, $id)
    {
        $this->_getResource()->getByProvider($this, $code, $id);

        return $this;
    }

    /**
     * Before save hook.
     *
     * @return $this
     */
    public function beforeSave()
    {
        $date = $this->_dateFactory->create()->gmtDate();

        if ($this->isObjectNew() && !$this->getCreatedAt()) {
            $this->setCreatedAt($date);
        }

        return parent::beforeSave();
    }
}
