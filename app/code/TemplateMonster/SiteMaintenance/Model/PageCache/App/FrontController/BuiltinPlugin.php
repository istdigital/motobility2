<?php

namespace TemplateMonster\SiteMaintenance\Model\PageCache\App\FrontController;

use Magento\PageCache\Model\App\FrontController\BuiltinPlugin as ParentBuiltinPlugin;

use TemplateMonster\SiteMaintenance\Helper\Data;

class BuiltinPlugin extends ParentBuiltinPlugin
{
    protected $_helper;

    public function __construct(
        \Magento\PageCache\Model\Config $config,
        \Magento\Framework\App\PageCache\Version $version,
        \Magento\Framework\App\PageCache\Kernel $kernel,
        \Magento\Framework\App\State $state,
        Data $data
    ) {
        $this->config = $config;
        $this->version = $version;
        $this->kernel = $kernel;
        $this->state = $state;
        $this->_helper = $data;
        parent::__construct($config, $version, $kernel, $state);
    }

    public function aroundDispatch(
        \Magento\Framework\App\FrontControllerInterface $subject,
        \Closure $proceed,
        \Magento\Framework\App\RequestInterface $request
    ) {
        if (!$this->_helper->isModuleActive()) {
            $result = parent::aroundDispatch($subject, $proceed, $request);
        } else {
            $result = $proceed($request);
        }
        return $result;
    }
}