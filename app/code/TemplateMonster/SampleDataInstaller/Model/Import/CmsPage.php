<?php

namespace TemplateMonster\SampleDataInstaller\Model\Import;

use Magento\Cms\Model\PageFactory;
use Magento\Cms\Model\PageRepository;
use Magento\Framework\File\Csv;
use Magento\Store\Api\StoreRepositoryInterface;
use Magento\Store\Api\WebsiteRepositoryInterface;

/**
 * CmsPage import model.
 *
 * @package TemplateMonster\SampleDataInstaller\Model\Import
 */
class CmsPage extends AbstractCmsImport
{
    /**
     * @var PageFactory
     */
    protected $_pageFactory;

    /**
     * @var PageRepository
     */
    protected $_pageRepository;

    /**
     * CmsPage constructor.
     *
     * @param PageFactory                $pageFactory
     * @param PageRepository             $blockRepository
     * @param WebsiteRepositoryInterface $websiteRepository
     * @param StoreRepositoryInterface   $storeRepository
     * @param Csv                        $csvReader
     * @param array                      $data
     */
    public function __construct(
        PageFactory $pageFactory,
        PageRepository $blockRepository,
        WebsiteRepositoryInterface $websiteRepository,
        StoreRepositoryInterface $storeRepository,
        Csv $csvReader,
        array $data = []
    ) {
        $this->_pageFactory = $pageFactory;
        $this->_pageRepository = $blockRepository;
        parent::__construct(
            $websiteRepository,
            $storeRepository,
            $csvReader, $data
        );
    }

    protected function _getEntityRepository()
    {
        return $this->_pageRepository;
    }

    /**
     * @return \Magento\Framework\ObjectManager\FactoryInterface
     */
    protected function _getEntityFactory()
    {
        return $this->_pageFactory;
    }
}