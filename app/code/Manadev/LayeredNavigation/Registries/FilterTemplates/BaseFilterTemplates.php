<?php
/**
 * @copyright   Copyright (c) http://www.manadev.com
 * @license     http://www.manadev.com/license  Proprietary License
 */

namespace Manadev\LayeredNavigation\Registries\FilterTemplates;

use Magento\Framework\ObjectManagerInterface;
use Manadev\Core\Exceptions\InterfaceNotImplemented;
use Manadev\Core\Features;
use Manadev\LayeredNavigation\Contracts\FilterTemplate;
use Manadev\LayeredNavigation\Contracts\FilterTemplates;
use Manadev\LayeredNavigation\Sources\TemplateSource;

abstract class BaseFilterTemplates implements FilterTemplates {
    /**
     * @var FilterTemplate[]
     */
    protected $filterTemplates;
    /**
     * @var ObjectManagerInterface
     */
    protected $objectManager;

    /**
     * @var TemplateSource
     */
    protected $source;
    /**
     * @var string
     */
    protected $defaultFilterTemplate;
    /**
     * @var Features
     */
    protected $features;

    public function __construct(ObjectManagerInterface $objectManager, Features $features,
        $defaultFilterTemplate, array $filterTemplates)
    {
        foreach ($filterTemplates as $filterTemplate) {
            if (!($filterTemplate instanceof FilterTemplate)) {
                throw new InterfaceNotImplemented(sprintf("'%s' does not implement '%s' interface.",
                    get_class($filterTemplate), FilterTemplate::class));
            }
        }
        $this->filterTemplates = $filterTemplates;
        $this->objectManager = $objectManager;
        $this->defaultFilterTemplate = $defaultFilterTemplate;
        $this->features = $features;
    }

    /**
     * Returns filter template by its internal name. Returns false if no filter template with specified name is
     * defined.
     *
     * @param $type
     * @return bool|FilterTemplate
     */
    public function get($type) {
        if (!isset($this->filterTemplates[$type])) {
            return $this->filterTemplates[$this->defaultFilterTemplate];
        }

        $result = $this->filterTemplates[$type];
        if (!$this->features->isEnabled(get_class($result))) {
            return $this->filterTemplates[$this->defaultFilterTemplate];
        }

        return $result;
    }

    /**
     * @return FilterTemplate[]
     */
    public function getList() {
        $self = $this;
        return array_filter($this->filterTemplates, function($filterTemplate) use ($self){
            return $self->features->isEnabled(get_class($filterTemplate), 0);
        });
    }

    /**
     * @return TemplateSource
     */
    public function getSource() {
        if (!$this->source) {
            $this->source = $this->objectManager->create('Manadev\LayeredNavigation\Sources\TemplateSource', [
                'filterTemplates' => $this
            ]);
        }

        return $this->source;
    }
}