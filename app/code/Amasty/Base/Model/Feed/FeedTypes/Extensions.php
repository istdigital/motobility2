<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2020 Amasty (https://www.amasty.com)
 * @package Amasty_Base
 */

declare(strict_types=1);

namespace Amasty\Base\Model\Feed\FeedTypes;

use Amasty\Base\Model\Feed\FeedContentProvider;
use Amasty\Base\Model\LinkValidator;
use Amasty\Base\Model\ModuleInfoProvider;
use Amasty\Base\Model\Parser;
use Amasty\Base\Model\Serializer;
use Magento\Framework\App\CacheInterface;
use Magento\Framework\Escaper;

class Extensions
{
    const EXTENSIONS_CACHE_ID = 'ambase_extensions';

    /**
     * @var Serializer
     */
    private $serializer;

    /**
     * @var CacheInterface
     */
    private $cache;

    /**
     * @var FeedContentProvider
     */
    private $feedContentProvider;

    /**
     * @var Parser
     */
    private $parser;

    /**
     * @var Escaper
     */
    private $escaper;

    /**
     * @var LinkValidator
     */
    private $linkValidator;

    /**
     * @var ModuleInfoProvider
     */
    private $moduleInfoProvider;

    public function __construct(
        Serializer $serializer,
        CacheInterface $cache,
        FeedContentProvider $feedContentProvider,
        Parser $parser,
        Escaper $escaper,
        LinkValidator $linkValidator,
        ModuleInfoProvider $moduleInfoProvider
    ) {
        $this->serializer = $serializer;
        $this->cache = $cache;
        $this->feedContentProvider = $feedContentProvider;
        $this->parser = $parser;
        $this->escaper = $escaper;
        $this->linkValidator = $linkValidator;
        $this->moduleInfoProvider = $moduleInfoProvider;
    }

    /**
     * @return array
     */
    public function execute(): array
    {
        if ($cache = $this->cache->load(self::EXTENSIONS_CACHE_ID)) {
            return $this->serializer->unserialize($cache);
        }

        return $this->getFeed();
    }

    /**
     * @return array
     */
    public function getFeed(): array
    {
        $result = [];
        $content = $this->feedContentProvider->getFeedContent(
            $this->feedContentProvider->getFeedUrl(FeedContentProvider::URN_EXTENSIONS)
        );
        $feedXml = $this->parser->parseXml($content);

        if (isset($feedXml->channel->item)) {
            $result = $this->prepareFeedData($feedXml);
        }
        $this->cache->save(
            $this->serializer->serialize($result),
            self::EXTENSIONS_CACHE_ID,
            [self::EXTENSIONS_CACHE_ID]
        );

        return $result;
    }

    /**
     * @param \SimpleXMLElement $feedXml
     * @return array
     */
    private function prepareFeedData(\SimpleXMLElement $feedXml): array
    {
        $marketplaceOrigin = $this->moduleInfoProvider->isOriginMarketplace();
        $result = [];

        foreach ($feedXml->channel->item as $item) {
            $code = $this->escaper->escapeHtml($item->code ?? '');

            if (!isset($result[$code])) {
                $result[$code] = [];
            }
            $title = $this->escaper->escapeHtml($item->title ?? '');
            $productPageLink = $marketplaceOrigin ? $item->market_link : $item->link;

            if (!$this->linkValidator->validate((string)$productPageLink)
                || !$this->linkValidator->validate((string)($item->guide ?? ''))
            ) {
                continue;
            }
            $result[$code][$title] = [
                'name' => $title,
                'url' => $this->escaper->escapeUrl((string)($productPageLink ?? '')),
                'version' => $this->escaper->escapeHtml((string)($item->version ?? '')),
                'conflictExtensions' => $this->escaper->escapeHtml((string)($item->conflictExtensions ?? '')),
                'guide' => $this->escaper->escapeUrl((string)($item->guide ?? ''))
            ];
        }

        return $result;
    }
}
