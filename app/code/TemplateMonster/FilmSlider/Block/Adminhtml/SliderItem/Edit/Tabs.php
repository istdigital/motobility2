<?php

/**
 *
 * Copyright © 2015 TemplateMonster. All rights reserved.
 * See COPYING.txt for license details.
 *
 */
namespace TemplateMonster\FilmSlider\Block\Adminhtml\SliderItem\Edit;

use Magento\Framework\DataObject;
use TemplateMonster\FilmSlider\Model\SliderItem;

class Tabs extends \Magento\Backend\Block\Widget\Tabs
{

    /**
     * @var \Magento\Framework\Registry
     */
    protected $_coreRegistry;

    /**
     * @var \TemplateMonster\FilmSlider\Api\SliderItemRepositoryInterface
     */
    protected $_sliderItemRepository;

    /**
     * @var \Magento\Framework\Api\SearchCriteriaBuilder
     */
    protected $_searchCriteriaBuilder;

    /**
     * @var \Magento\Framework\Api\FilterBuilder
     */
    protected $_filterBuilder;

    public function __construct(\Magento\Backend\Block\Template\Context $context,
                                \Magento\Framework\Json\EncoderInterface $jsonEncoder,
                                \Magento\Backend\Model\Auth\Session $authSession,
                                \TemplateMonster\FilmSlider\Api\SliderItemRepositoryInterface $sliderItemRepository,
                                \Magento\Framework\Api\SearchCriteriaBuilder $searchCriteriaBuilder,
                                \Magento\Framework\Api\FilterFactory $filterBuilder,
                                \Magento\Framework\Registry $registry,
                                array $data = [])
    {
        $this->_sliderItemRepository = $sliderItemRepository;
        $this->_searchCriteriaBuilder = $searchCriteriaBuilder;
        $this->_filterBuilder = $filterBuilder;
        $this->_coreRegistry = $registry;
        parent::__construct($context, $jsonEncoder, $authSession, $data);
    }

    /**
     * @return void
     */
    protected function _construct()
    {
        parent::_construct();
        $this->setId('slider_items_tabs');
        $this->setDestElementId('edit_form');
        $this->setTitle(__('Slider Item Information'));
    }

    protected function _beforeToHtml()
    {
        $sliderModel = $this->_coreRegistry->registry(\TemplateMonster\FilmSlider\Model\Slider::REGISTRY_NAME);
        $sliderItemModel = $this->_coreRegistry->registry(\TemplateMonster\FilmSlider\Model\SliderItem::REGISTRY_NAME);

        $filters = [];
        if ($sliderModel && $sliderModel->getId()) {
            $this->_searchCriteriaBuilder->setCurrentPage(0);
            $this->_searchCriteriaBuilder->setPageSize(100);

            $filter = $this->_filterBuilder->create();
            $filters[] = $filter
                ->setField('parent_id')
                ->setConditionType('eq')
                ->setValue($sliderModel->getId());

            if ($sliderItemModel && $sliderItemModel->getId()) {
                $filter = $this->_filterBuilder->create();
                $filters[] = $filter
                    ->setField('slideritem_id')
                    ->setConditionType('neq')
                    ->setValue($sliderItemModel->getId());
            }

            $this->_searchCriteriaBuilder->addFilters($filters);
            $searchCriteria = $this->_searchCriteriaBuilder->create();
            $sliderItems =  $this->_sliderItemRepository->getList($searchCriteria);


            if ($sliderModel && $sliderModel->getId()) {
                $this->addTab('add_new_slide',
                    [
                        'id'=>'add-new-slide',
                        'title'=>__('Add New Slide'),
                        'label'=>__('Add New Slide'),
                        'url'=>$this->getUrl('filmslider/slideritem/new', ['parent_id'=>$sliderModel->getId()])
                    ]);
            }

            foreach ($sliderItems->getItems() as $item) {
                $objData = new DataObject($item);

                if ($objData->getId() && $objData->getTitle()) {
                    $this->addTab('slide_item_'.$objData->getId(),
                        [
                            'id'=>'add-new-slide-'.$objData->getId(),
                            'title'=>__($objData->getTitle()),
                            'label'=>__($objData->getTitle()),
                            'url'=>$this->getUrl('filmslider/slideritem/edit', ['slideritem_id'=>$objData->getId()])
                        ]);
                }
            }
        }

        parent::_beforeToHtml();
    }
}
