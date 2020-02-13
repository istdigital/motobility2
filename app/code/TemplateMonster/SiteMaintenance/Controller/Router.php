<?php
namespace TemplateMonster\SiteMaintenance\Controller;

use Magento\Framework\App\ActionFactory;
use Magento\Framework\App\RequestInterface;

use TemplateMonster\SiteMaintenance\Helper\Data;

class Router implements \Magento\Framework\App\RouterInterface
{
    const MODULE_PATH = 'maintenance';

    protected $_actionFactory;

    protected $_helper;

    protected $messageManager;

    protected $_exceptionalFrontNames = ['theme_options', 'newsletter_popup'];

    public function __construct(
        ActionFactory $actionFactory,
        Data $helper
    ) {
        $this->_actionFactory = $actionFactory;
        $this->_helper = $helper;
    }

    /**
     * Check module active and whitelist
     *
     * @param \Magento\Framework\App\RequestInterface $request
     * @return \Magento\Framework\App\ActionInterface|null
     */
    public function match(
        RequestInterface $request
    ) {
        if (!$this->_helper->isModuleActive()) {
            return null;
        }

        $frontName = $request->getFrontName();

        if ($frontName == self::MODULE_PATH) {
            return null;
        }

        // do not use router for ThemeOptions and NewsletterPopup modules
        if (in_array($frontName, $this->_exceptionalFrontNames)) {
            return null;
        }

        $whitelist = $this->_helper->getWhitelist();
        $ip = $this->_helper->getClientIp();

        if (!in_array($ip, $whitelist)) {
            $request->setPathInfo(self::MODULE_PATH);
            return $this->_actionFactory->create('Magento\Framework\App\Action\Forward');
        }
        return null;
    }
}
