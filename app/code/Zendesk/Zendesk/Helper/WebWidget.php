<?php

namespace Zendesk\Zendesk\Helper;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\Helper\Context;

class WebWidget extends \Magento\Framework\App\Helper\AbstractHelper
{
    const WEB_WIDGET_SNIPPET_CACHE_CONFIG_PATH_PREFIX = 'zendesk/web_widget/saved_widget_snippet';

    /**
     * @var Config
     */
    protected $configHelper;
    /**
     * @var \Magento\Framework\HTTP\ZendClientFactory
     */
    protected $httpClientFactory;
    /**
     * @var \Magento\Framework\App\Config\Storage\WriterInterface
     */
    protected $configWriter;
    /**
     * @var \Magento\Framework\App\Cache\Manager
     */
    protected $cacheManager;

    /**
     * WebWidget constructor.
     * @param Context $context
     * @param Config $configHelper
     * @param \Magento\Framework\HTTP\ZendClientFactory $httpClientFactory
     * @param \Magento\Framework\App\Config\Storage\WriterInterface $configWriter
     * @param \Magento\Framework\App\Cache\Manager $cacheManager
     */
    public function __construct(
        Context $context,
        // End parent parameters
        \Zendesk\Zendesk\Helper\Config $configHelper,
        \Magento\Framework\HTTP\ZendClientFactory $httpClientFactory,
        \Magento\Framework\App\Config\Storage\WriterInterface $configWriter,
        \Magento\Framework\App\Cache\Manager $cacheManager
    ) {
        parent::__construct($context);
        $this->configHelper = $configHelper;
        $this->httpClientFactory = $httpClientFactory;
        $this->configWriter = $configWriter;
        $this->cacheManager = $cacheManager;
    }

    /**
     * Get web widget snippet by making live query to Zendesk API.
     *
     * @param string $domain
     * @return string
     */
    protected function doGetWebWidgetSnippet($domain)
    {
        $url = str_replace(
            \Zendesk\Zendesk\Helper\Config::DOMAIN_PLACEHOLDER,
            $domain,
            $this->configHelper->getWebWidgetDynamicSnippetUrlPattern()
        );

        /** @var \Magento\Framework\HTTP\ZendClient $httpClient */
        $httpClient = $this->httpClientFactory->create(['uri' => $url]);

        try {
            $response = $httpClient->request();

            return $response->getBody();
        } catch (\Exception $ex) {
            // Intentionally swallow exception to avoid breaking pages.

            return '';
        }
    }

    /**
     * Get subdomain-specific snippet config path
     *
     * @param string $subdomain
     * @return string
     */
    protected function getDomainSpecificSnippetConfigPath($subdomain)
    {
        return sprintf('%s_%s', self::WEB_WIDGET_SNIPPET_CACHE_CONFIG_PATH_PREFIX, $subdomain);
    }

    /**
     * Get web widget snippet saved in config, if any.
     *
     * @param string $subdomain
     * @param string $scopeType
     * @param null $scopeCode
     * @return string
     */
    protected function getConfigSavedSnippet(
        $subdomain,
        $scopeType = ScopeConfigInterface::SCOPE_TYPE_DEFAULT,
        $scopeCode = null
    ) {
        return (string)$this->scopeConfig->getValue(
            $this->getDomainSpecificSnippetConfigPath($subdomain),
            $scopeType,
            $scopeCode
        );
    }

    /**
     * Persist web widget snippet in hidden config field value
     * to cache value indefinitely.
     *
     * @param string $subdomain
     * @param string $snippet
     * @param string $scopeType
     * @param int $scopeCode
     */
    protected function setConfigSavedSnippet(
        $subdomain,
        $snippet,
        $scopeType = ScopeConfigInterface::SCOPE_TYPE_DEFAULT,
        $scopeCode = 0
    ) {
        $this->configWriter->save(
            $this->getDomainSpecificSnippetConfigPath($subdomain),
            $snippet,
            $scopeType,
            (int)$scopeCode
        );

        // Newly set config value won't take effect unless config cache is cleaned.
        $this->cacheManager->clean([\Magento\Framework\App\Cache\Type\Config::TYPE_IDENTIFIER]);
    }

    /**
     * Get web widget snippet, taking saved configured value into account.
     *
     * @param string $scopeType
     * @param null $scopeCode
     * @return string
     */
    public function getWebWidgetSnippet(
        $scopeType = ScopeConfigInterface::SCOPE_TYPE_DEFAULT,
        $scopeCode = null
    ) {
        $domain = $this->configHelper->getDomain($scopeType, $scopeCode);

        if (empty($domain)) {
            return ''; // Zendesk domain not configured.
        }

        // Since subdomain is a function of domain, it must be configured at this point

        $subdomain = $this->configHelper->getSubDomain($scopeType, $scopeCode);

        $snippet = $this->getConfigSavedSnippet($subdomain, $scopeType, $scopeCode);

        if (empty($snippet)) {
            // Must be the first time the snippet has been requested.
            // Perform live lookup, then save value in config.

            $snippet = $this->doGetWebWidgetSnippet($domain);
            $this->setConfigSavedSnippet($subdomain, $snippet, $scopeType, $scopeCode);
        }

        return $snippet;
    }

    /**
     * Enable web widget configuration setting
     *
     * @param bool $enabled
     * @param string $scopeType
     * @param int $scopeId
     */
    public function toggleWebWidget(
        $enabled,
        $scopeType = \Magento\Framework\App\Config\ScopeConfigInterface::SCOPE_TYPE_DEFAULT,
        $scopeId = 0
    ) {
        // NB: it would be a lot cleaner to simply save the value of $enabled.
        // However, this could leave the setting explicitly disabled at a store view scope
        // if later enabled at a global scope, which could be confusing for the user.
        // Since the default value in config.xml is disabled, removing the explicit value
        // in the case it is intended to be disabled results in a more intuitive user experience.

        if ($enabled) {
            $this->configWriter->save(
                \Zendesk\Zendesk\Helper\Config::WEB_WIDGET_ENABLED_CONFIG_PATH,
                1,
                $scopeType,
                $scopeId
            );
        } else {
            $this->configWriter->delete(
                \Zendesk\Zendesk\Helper\Config::WEB_WIDGET_ENABLED_CONFIG_PATH,
                $scopeType,
                $scopeId
            );
        }

        // Newly set config value won't take effect unless config cache is cleaned.
        $this->cacheManager->clean([\Magento\Framework\App\Cache\Type\Config::TYPE_IDENTIFIER]);
    }
}
