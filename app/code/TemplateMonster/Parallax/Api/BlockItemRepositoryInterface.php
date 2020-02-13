<?php

namespace TemplateMonster\Parallax\Api;

use TemplateMonster\Parallax\Api\Data\BlockItemInterface;
use Magento\Framework\Api\SearchCriteriaInterface;

/**
 * Block item api interface.
 *
 * @package TemplateMonster\Parallax\Api
 */
interface BlockItemRepositoryInterface
{
    /**
     * Save block item entity.
     *
     * @param BlockItemInterface $blockItem
     *
     * @return mixed
     */
    public function save(BlockItemInterface $blockItem);

    /**
     * Get block item entity by id.
     *
     * @param int $id
     *
     * @return BlockItemInterface
     */
    public function getById($id);

    /**
     * Delete block item entity.
     *
     * @param BlockItemInterface $blockItem
     *
     * @return mixed
     */
    public function delete(BlockItemInterface $blockItem);

    /**
     * Delete block item entity by id.
     *
     * @param int $id
     *
     * @return mixed
     */
    public function deleteById($id);

    /**
     * Get list of block items by search criteria.
     *
     * @param \Magento\Framework\Api\SearchCriteriaInterface $searchCriteria
     *
     * @return \TemplateMonster\Parallax\Api\Data\BlockItemSearchResultsInterface
     *
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getList(SearchCriteriaInterface $searchCriteria);

    /**
     * Create block item model instance.
     *
     * @return BlockItemInterface
     */
    public function getModelInstance();
}
