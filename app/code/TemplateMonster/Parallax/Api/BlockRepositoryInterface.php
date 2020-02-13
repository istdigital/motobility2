<?php

namespace TemplateMonster\Parallax\Api;

use TemplateMonster\Parallax\Api\Data\BlockInterface;
use Magento\Framework\Api\SearchCriteriaInterface;

/**
 * Interface BlockRepositoryInterface
 *
 * @package TemplateMonster\Parallax\Api
 */
interface BlockRepositoryInterface
{
    /**
     * Save block entity.
     *
     * @param BlockInterface $block
     *
     * @return mixed
     */
    public function save(BlockInterface $block);

    /**
     * Get block entity by id.
     *
     * @param int $id
     *
     * @return BlockInterface
     */
    public function getById($id);

    /**
     * Delete block item entity.
     *
     * @param BlockInterface $block
     *
     * @return mixed
     */
    public function delete(BlockInterface $block);

    /**
     * Delete block entity by id.
     *
     * @param int $id
     *
     * @return mixed
     */
    public function deleteById($id);

    /**
     * Get list of blocks by search criteria.
     *
     * @param \Magento\Framework\Api\SearchCriteriaInterface $searchCriteria
     *
     * @return \TemplateMonster\Parallax\Api\Data\BlockSearchResultsInterface
     *
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getList(SearchCriteriaInterface $searchCriteria);

    /**
     * Create block model instance.
     *
     * @return BlockInterface
     */
    public function getModelInstance();
}
