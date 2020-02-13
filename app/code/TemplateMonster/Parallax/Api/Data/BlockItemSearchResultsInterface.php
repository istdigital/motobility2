<?php

namespace TemplateMonster\Parallax\Api\Data;

/**
 * Block item search results interface.
 *
 * @package TemplateMonster\Parallax\Api\Data
 */
interface BlockItemSearchResultsInterface
{
    /**
     * Get blocks list.
     *
     * @return \TemplateMonster\Parallax\Api\Data\BlockItemInterface[]
     */
    public function getItems();

    /**
     * Set blocks list.
     *
     * @param \TemplateMonster\Parallax\Api\Data\BlockItemInterface[] $items
     * @return $this
     */
    public function setItems(array $items);
}