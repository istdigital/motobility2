<?php

namespace TemplateMonster\SocialLogin\Model\ResourceModel\Provider;

use TemplateMonster\SocialLogin\Helper\Data as SocialLoginHelper;
use TemplateMonster\SocialLogin\Model\Exception;
use TemplateMonster\SocialLogin\Model\Provider;
use Magento\Framework\Config\Reader\Filesystem;
use Magento\Framework\Data\Collection as DataCollection;
use Magento\Framework\Data\Collection\EntityFactoryInterface;
use Magento\Framework\DataObject;
use Magento\Framework\ObjectManagerInterface;
use TemplateMonster\SocialLogin\Model\ProviderInterface;

/**
 * Provider collection.
 */
class Collection extends DataCollection
{
    /**
     * @var string
     */
    protected $_itemObjectClass = 'TemplateMonster\SocialLogin\Model\Provider';

    /**
     * @var Filesystem
     */
    protected $_reader;

    /**
     * @var ObjectManagerInterface
     */
    protected $_objectManager;

    /**
     * @var SocialLoginHelper
     */
    protected $_helper;

    /**
     * Collection constructor.
     *
     * @param Filesystem             $reader
     * @param SocialLoginHelper      $socialLoginHelper
     * @param ObjectManagerInterface $objectManager
     * @param EntityFactoryInterface $entityFactory
     */
    public function __construct(
        Filesystem $reader,
        SocialLoginHelper $socialLoginHelper,
        ObjectManagerInterface $objectManager,
        EntityFactoryInterface $entityFactory
    ) {
        $this->_reader = $reader;
        $this->_helper = $socialLoginHelper;
        $this->_objectManager = $objectManager;
        parent::__construct($entityFactory);
    }

    /**
     * @param bool $printQuery
     * @param bool $logQuery
     *
     * @return $this
     *
     * @throws \Exception
     */
    public function loadData($printQuery = false, $logQuery = false)
    {
        if ($this->isLoaded()) {
            return $this;
        }

        foreach ($this->_reader->read() as $provider => $data) {
            /** @var DataObject|ProviderInterface $provider */
            $provider = $this->_objectManager->create(
                $data['class'],
                $this->_getProviderOptions($provider)
            );
            if ($provider->isEnabled()) {
                $this->addItem($provider);
            }
        }
        $this->sortByOrder();

        $this->_setIsLoaded(true);
    }

    /**
     * @param mixed $idValue
     *
     * @return DataObject|ProviderInterface
     *
     * @throws Exception
     */
    public function getItemById($idValue)
    {
        $item = parent::getItemById($idValue);
        if (null === $item) {
            throw new Exception(
                "Social provider \"{$idValue}\" not found.",
                Exception::TYPE_PROVIDER_NOT_FOUND
            );
        }

        return $item;
    }

    /**
     * Sort by order.
     *
     * @return $this
     */
    public function sortByOrder()
    {
        uasort($this->_items, [$this, '_sortComparator']);

        return $this;
    }

    /**
     * Sort comparator function.
     *
     * @param ProviderInterface $first
     * @param ProviderInterface $second
     *
     * @return int
     */
    protected function _sortComparator(ProviderInterface $first, ProviderInterface $second)
    {
        if ($first->getSortOrder() == $second->getSortOrder()) {
            return 0;
        }

        return $first->getSortOrder() < $second->getSortOrder() ? -1 : 1;
    }

    /**
     * @param string $provider
     *
     * @return array
     */
    protected function _getProviderOptions($provider)
    {
        return ['options' => $this->_helper->getProviderOptions($provider)];
    }

    /**
     * @param DataObject $item
     *
     * @return mixed
     */
    protected function _getItemId(DataObject $item)
    {
        return $item->getCode();
    }
}
