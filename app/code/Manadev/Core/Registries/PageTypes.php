<?php
/**
 * @copyright   Copyright (c) http://www.manadev.com
 * @license     http://www.manadev.com/license  Proprietary License
 */

namespace Manadev\Core\Registries;

use Magento\Framework\ObjectManagerInterface;
use Manadev\Core\Contracts\PageType;
use Manadev\Core\Exceptions\InterfaceNotImplemented;

class PageTypes
{
    /**
     * @var PageType[]
     */
    protected $pageTypes;
    /**
     * @var ObjectManagerInterface
     */
    protected $objectManager;

    public function __construct(ObjectManagerInterface $objectManager, array $pageTypes)
    {
        $this->objectManager = $objectManager;
        $this->pageTypes = [];

        foreach ($pageTypes as $route => $pageType) {
            if (!($pageType instanceof PageType)) {
                throw new InterfaceNotImplemented(sprintf("'%s' does not implement '%s' interface.",
                    get_class($pageType), PageType::class));
            }

            $this->pageTypes[$route] = $pageType;
            $pageType->setRoute($route);
        }
    }

    /**
     * @param $route
     * @return PageType
     */
    public function get($route) {
        return isset($this->pageTypes[$route]) ? $this->pageTypes[$route] : null;
    }

    /**
     * @return PageType[]
     */
    public function getList() {
        return $this->pageTypes;
    }
}