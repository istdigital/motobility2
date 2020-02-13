<?php

namespace TemplateMonster\Parallax\Api\Data;

/**
 * Block search results interface.
 *
 * @package TemplateMonster\Parallax\Api\Data
 */
interface BlockSearchResultsInterface
{
    /**
     * Get blocks list.
     *
     * @return \TemplateMonster\Parallax\Api\Data\BlockInterface[]
     */
    public function getItems();

    /**
     * Set blocks list.
     *
     * @param \TemplateMonster\Parallax\Api\Data\BlockInterface[] $items
     * @return $this
     */
    public function setItems(array $items);
}