<?php

namespace TemplateMonster\SampleDataInstaller\Model\Import;

use Magento\Cms\Api\BlockRepositoryInterface;
use Magento\Cms\Model\BlockFactory;
use Magento\Framework\File\Csv;
use Magento\Store\Api\StoreRepositoryInterface;
use Magento\Store\Api\WebsiteRepositoryInterface;

/**
 * CmsBlock import model.
 *
 * @package TemplateMonster\SampleDataInstaller\Model\Import
 */
class CmsBlock extends AbstractCmsImport
{
    /**
     * @var BlockFactory
     */
    protected $_blockFactory;

    /**
     * @var BlockRepositoryInterface
     */
    protected $_blockRepository;

    /**
     * CmsBlock constructor.
     *
     * @param BlockFactory               $pageFactory
     * @param BlockRepositoryInterface   $blockRepository
     * @param WebsiteRepositoryInterface $websiteRepository
     * @param StoreRepositoryInterface   $storeRepository
     * @param Csv                        $csvReader
     * @param array                      $data
     */
    public function __construct(
        BlockFactory $pageFactory,
        BlockRepositoryInterface $blockRepository,
        WebsiteRepositoryInterface $websiteRepository,
        StoreRepositoryInterface $storeRepository,
        Csv $csvReader,
        array $data = []
    ) {
        $this->_blockFactory = $pageFactory;
        $this->_blockRepository = $blockRepository;
        parent::__construct(
            $websiteRepository,
            $storeRepository,
            $csvReader, $data
        );
    }

    /**
     * @inheritdoc
     */
    protected function _getEntityRepository()
    {
        return $this->_blockRepository;
    }

    /**
     * @inheritdoc
     */
    protected function _getEntityFactory()
    {
        return $this->_blockFactory;
    }
}