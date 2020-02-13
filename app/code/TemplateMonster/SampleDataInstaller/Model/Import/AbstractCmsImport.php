<?php

namespace TemplateMonster\SampleDataInstaller\Model\Import;

use Magento\Framework\File\Csv;
use Magento\Framework\DataObject;
use Magento\Store\Api\StoreRepositoryInterface;
use Magento\Store\Api\WebsiteRepositoryInterface;

/**
 * Class AbstractCmsImport
 *
 * @method string getWebsite()
 * @method string getStore()
 * @method bool getIsOverride()
 * @method array getImportFiles()
 *
 * @package TemplateMonster\SampleDataInstaller\Model\Import
 */
abstract class AbstractCmsImport extends DataObject implements ImportInterface
{
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
     * CmsPage constructor.
     *
     * @param WebsiteRepositoryInterface $websiteRepository
     * @param StoreRepositoryInterface   $storeRepository
     * @param Csv                        $csvReader
     * @param array                      $data
     */
    public function __construct(
        WebsiteRepositoryInterface $websiteRepository,
        StoreRepositoryInterface $storeRepository,
        Csv $csvReader,
        array $data = []
    )
    {
        $this->_websiteRepository = $websiteRepository;
        $this->_storeRepository = $storeRepository;
        $this->_csvReader = $csvReader;
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
            $samples = $this->_getSamples($importFile);
            $entities = $this->_getEntities();

            foreach ($samples as $sample) {
                $identifier = $sample['identifier'];
                if (!$this->getIsOverride() && isset($entities[$identifier])) {
                    $skipped++;
                    continue;
                }

                /** @var \Magento\Cms\Model\Page|\Magento\Cms\Model\Block $entity */
                if ($this->getIsOverride() && isset($entities[$identifier])) {
                    $entity = $entities[$identifier];
                }
                else {
                    $entity = $this->_getEntityFactory()->create();
                }
                $entity->addData($sample);
                $entity->setStoreId($this->_getStoreIds());

                $this->_getEntityRepository()->save($entity);
                $imported++;
            }
        }

        return [$imported, $skipped];
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
        $data = $this->_csvReader->getData($importFile);
        $headers = array_shift($data);
        foreach ($data as &$row) {
            $row = array_combine($headers, array_map([$this, '_fixNullValue'], $row));
            foreach (['page_id', 'block_id'] as $primaryKey) {
                unset ($row[$primaryKey]);
            }
        }

        return $data;
    }

    /**
     * Get pages.
     *
     * @return array
     */
    protected function _getEntities()
    {
        $collection = $this->_getEntityFactory()->create()->getCollection();
        $entities = [];
        /** @var \Magento\Cms\Model\Page|\Magento\Cms\Model\Block $entity */
        foreach ($collection as $entity) {
            $entities[$entity->getIdentifier()] = $entity;
        }

        return $entities;
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

    /**
     * Get entity repository.
     *
     * @return \Magento\Cms\Api\PageRepositoryInterface | \Magento\Cms\Api\BlockRepositoryInterface
     */
    protected abstract function _getEntityRepository();

    /**
     * Get entity factory.
     *
     * @return \Magento\Cms\Model\PageFactory | \Magento\Cms\Model\BlockFactory;
     */
    protected abstract function _getEntityFactory();
}