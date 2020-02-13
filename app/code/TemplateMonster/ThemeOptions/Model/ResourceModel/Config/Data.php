<?php

namespace TemplateMonster\ThemeOptions\Model\ResourceModel\Config;

use Magento\Config\Model\ResourceModel\Config\Data as BaseData;

/**
 * Config data extended resource collection.
 *
 * @package TemplateMonster\ThemeOptions\Model\ResourceModel\Config
 */
class Data extends BaseData
{
    /**
     * Clear section data.
     *
     * @param string $section
     *
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function clearSectionData($section)
    {
        $this->getConnection()->delete(
            $this->getMainTable(),
            ['path LIKE ?' => "$section/%"]
        );
    }
}