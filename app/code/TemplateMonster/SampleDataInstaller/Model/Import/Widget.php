<?php

namespace TemplateMonster\SampleDataInstaller\Model\Import;

use Magento\Framework\View\DesignInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\File\Csv;
use Magento\Framework\DataObject;
use Magento\Store\Api\StoreRepositoryInterface;
use Magento\Store\Api\WebsiteRepositoryInterface;
use Magento\Store\Model\ScopeInterface;
use Magento\Theme\Model\DesignConfigRepository;
use Magento\Widget\Model\ResourceModel\Widget\Instance\CollectionFactory as InstanceCollectionFactory;
use Magento\Widget\Model\Widget\InstanceFactory;

/**
 * Widget import model.
 *
 * @method string getWebsite()
 * @method string getStore()
 * @method bool getIsOverride()
 * @method array getImportFiles()
 *
 * @package TemplateMonster\SampleDataInstaller\Model\Import
 */
class Widget extends DataObject implements ImportInterface
{
    /**
     * @var DesignConfigRepository
     */
    protected $_designConfigRepository;

    /**
     * @var InstanceCollectionFactory
     */
    protected $_instanceCollectionFactory;

    /**
     * @var InstanceFactory
     */
    protected $_instanceFactory;

    /**
     * @var WebsiteRepositoryInterface
     */
    protected $_websiteRepository;

    /**
     * @var StoreRepositoryInterface
     */
    protected $_storeRepository;

    /**
     * @var Csv
     */
    protected $_csvReader;

    /**
     * @var ScopeConfigInterface
     */
    protected $_scopeConfig;

    /**
     * Widget constructor.
     *
     * @param DesignConfigRepository     $designConfigRepository
     * @param InstanceCollectionFactory  $instanceCollectionFactory
     * @param InstanceFactory            $instanceFactory
     * @param WebsiteRepositoryInterface $websiteRepository
     * @param StoreRepositoryInterface   $storeRepository
     * @param Csv                        $csvReader
     * @param ScopeConfigInterface       $scopeConfig
     * @param array                      $data
     */
    public function __construct(
        DesignConfigRepository $designConfigRepository,
        InstanceCollectionFactory $instanceCollectionFactory,
        InstanceFactory $instanceFactory,
        WebsiteRepositoryInterface $websiteRepository,
        StoreRepositoryInterface $storeRepository,
        Csv $csvReader,
        ScopeConfigInterface $scopeConfig,
        array $data = []
    ) {
        $this->_designConfigRepository = $designConfigRepository;
        $this->_instanceCollectionFactory = $instanceCollectionFactory;
        $this->_instanceFactory = $instanceFactory;
        $this->_websiteRepository = $websiteRepository;
        $this->_storeRepository = $storeRepository;
        $this->_csvReader = $csvReader;
        $this->_scopeConfig = $scopeConfig;
        parent::__construct($data);
    }

    /**
     * Perform import.
     *
     * @return array
     * @throws \Magento\Framework\Exception\CouldNotSaveException
     */
    public function import()
    {
        $imported = $skipped = 0;

        foreach ($this->getImportFiles() as $importFile) {
            $entities = $this->_getEntities();
            list($instances, $pageGroups) = $this->_getSamples($importFile);

            foreach ($instances as $instance) {
                $id = $instance['instance_id'];
                $title = $instance['title'];
                unset($instance['instance_id']);

                if (!$this->getIsOverride() && isset($entities[$title])) {
                    $skipped++;
                    continue;
                }

                /** @var \Magento\Widget\Model\Widget\Instance $entity */
                if ($this->getIsOverride() && isset($entities[$title])) {
                    $entity = $entities[$title];
                }
                else {
                    $entity = $this->_instanceFactory->create();
                }

                $entity
                    ->addData($instance)
                    ->setStoreIds($this->_getStoreIds())
                    ->setThemeId($this->_getThemeId())
                ;
                if (isset($pageGroups[$id])) {
                    $entity->setPageGroups($pageGroups[$id]);
                }
                $entity->save();

                $imported++;
            }
        }

        return [$imported, $skipped];
    }

    /**
     * Get widget instances.
     *
     * @return array
     */
    protected function _getEntities()
    {
        $collection = $this->_instanceCollectionFactory->create();
        $entities = [];
        /** @var \Magento\Widget\Model\ResourceModel\Widget\Instance $entity */
        foreach ($collection as $entity) {
            $entities[$entity->getTitle()] = $entity;
        }

        return $entities;
    }

    /**
     * Get samples.
     *
     * @param $importFile
     *
     * @return array
     * @throws \Exception
     */
    protected function _getSamples($importFile)
    {
        $instances = $this->_csvReader->getData($importFile);
        $headers = array_shift($instances);
        foreach ($instances as &$instance) {
            $instance = array_combine($headers, array_map([$this, '_fixNullValue'], $instance));
        }

        $rows = $this->_csvReader->getData($importFile . '.page');
        $headers = array_shift($rows);
        $pages = [];
        foreach ($rows as $row) {
            $item = array_combine($headers, array_map([$this, '_fixNullValue'], $row));

            $id = $item['instance_id'];
            if (!isset($pages[$id])) {
                $pages[$id] = [];
            }

            $pages[$id][] = [
                'page_group' => $item['page_group'],
                $item['page_group'] => $this->_remapPageGroup($item)
            ];
        }

        return [$instances, $pages];
    }

    /**
     * Get store ids.
     *
     * @return array
     */
    protected function _getStoreIds()
    {
        if ($this->getStore()) {
            return (array) $this->getStore();
        }

        $id = $this->getWebsite();
        /** @var \Magento\Store\Model\Website $website */
        $website = $this->_websiteRepository->getById($id);

        return $website->getStoreIds();
    }

    /**
     * Get theme id.
     *
     * @return integer
     */
    protected function _getThemeId()
    {
        if ($this->getStore()) {
            $scopeType = ScopeInterface::SCOPE_STORE;
            $scopeCode = $this->getStore();
        }
        else  {
            $scopeType = ScopeInterface::SCOPE_WEBSITE;
            $scopeCode = $this->getWebsite();
        }

        return $this->_scopeConfig->getValue(
            DesignInterface::XML_PATH_THEME_ID,
            $scopeType,
            $scopeCode
        );
    }

    /**
     * @param $item
     *
     * @return mixed
     */
    protected function _remapPageGroup($item)
    {
        $item['for'] = $item['page_for'];
        $item['block'] = $item['block_reference'];
        $item['template'] = $item['page_template'];

        return $item;
    }

    /**
     * Fix null value.
     *
     * @param mixed $data
     *
     * @return mixed
     */
    protected function _fixNullValue($data)
    {
        if (strcasecmp('null', $data) === 0) {
            return null;
        }

        return $data;
    }
}