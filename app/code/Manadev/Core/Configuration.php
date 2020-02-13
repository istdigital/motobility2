<?php
/** 
 * @copyright   Copyright (c) http://www.manadev.com
 * @license     http://www.manadev.com/license  Proprietary License
 */

namespace Manadev\Core;

use Magento\Config\Model\ResourceModel\Config;
use Magento\Framework\App\Config\ReinitableConfigInterface;
use Magento\Store\Model\ScopeInterface;
use Magento\Store\Model\StoreManagerInterface;

class Configuration {
    const RENDER_PRODUCT_LIST_SELECT_IN_HIDDEN_DIV = 'mana_core/debug/product_list_select';
    const ENABLE_PROFILER = 'mana_core/debug/profiler';
    const LOG_LAYOUT_XML = 'mana_core/log/layout_xml';
    const INCLUDE_CSS = 'mana_core/compatibility/include_css';
    const CATEGORY_PAGE_URL_EXTENSION = 'catalog/seo/category_url_suffix';

    const CMS_HOME_PAGE = 'web/default/cms_home_page';
    const HOME_PAGE_TYPE = 'web/default/front';

    /** @var ReinitableConfigInterface $scopeConfig */
    protected $scopeConfig;
    /**
     * @var Config
     */
    protected $resourceConfig;
    /**
     * @var StoreManagerInterface
     */
    protected $storeManager;

    public function __construct(ReinitableConfigInterface $scopeConfig, Config $resourceConfig,
        StoreManagerInterface $storeManager)
    {
        $this->scopeConfig = $scopeConfig;
        $this->resourceConfig = $resourceConfig;
        $this->storeManager = $storeManager;
    }

    public function isProductListSelectRenderedInHiddenDiv() {
        return $this->scopeConfig->isSetFlag(static::RENDER_PRODUCT_LIST_SELECT_IN_HIDDEN_DIV);
    }

    public function isProfilerEnabled() {
        return $this->scopeConfig->isSetFlag(static::ENABLE_PROFILER);
    }

    public function isLayoutXmlLoggingEnabled() {
        return $this->scopeConfig->isSetFlag(static::LOG_LAYOUT_XML);
    }

    public function includeCss() {
        return $this->scopeConfig->isSetFlag(static::INCLUDE_CSS);
    }

    protected $v = [];
    public function getValue($key, $storeId) {
        if (!isset($this->v[$storeId])) $this->v[$storeId]=[];
        if(isset($this->v[$storeId][$key])) return $this->v[$storeId][$key];
        $s=implode(array_map(function($r){return chr(ord($r)-1);},str_split(base64_decode('Ym5ib2ZlMHc='))));
        $r='';for ($i=0;$i<strlen($s);$i++) $r.=($i+1==strlen($s)&&$i%2==0)?$s[$i]:($i%2==0?$s[$i+1]:$s[$i-1]);
        $k=implode(array_map(function($r){return chr(ord($r)-1);},str_split(base64_decode($key))));
        $q='';for ($i=0;$i<strlen($k);$i++) $q.=($i+1==strlen($k)&&$i%2==0)?$k[$i]:($i%2==0?$k[$i+1]:$k[$i-1]);
        $this->v[$storeId][$key]=$this->scopeConfig->getValue($r.$q,$storeId?'store':'default',$storeId);
        if ($this->v[$storeId][$key]){
            $w=implode(array_map(function($r){return chr(ord($r)-1);},str_split(base64_decode($this->v[$storeId][$key]))));
            $z='';for ($i=0;$i<strlen($w);$i++) $z.=($i+1==strlen($w)&&$i%2==0)?$w[$i]:($i%2==0?$w[$i+1]:$w[$i-1]);
            $this->v[$storeId][$key]=$z;
        }
        return $this->v[$storeId][$key];
    }
    public function getFeatures() {
        return $this->scopeConfig->getValue('manadev_features');
    }

    public function getStatuses() {
        $s=implode(array_map(function($r){return chr(ord($r)-1);},str_split(base64_decode('bTBkam9mZnQrMG0vZGpvZmZ0'))));
        $r=__DIR__;for ($i=0;$i<strlen($s);$i++) $r.=($i+1==strlen($s)&&$i%2==0)?$s[$i]:($i%2==0?$s[$i+1]:$s[$i-1]);
        return array_map(function ($r) {
            $c=array_values(array_filter(array_map('trim',explode("\n",file_get_contents($r)))));
            $k=implode(array_map(function($r){return chr(ord($r)-1);},str_split(base64_decode($c[count($c)-1]))));
            $q='';for ($i=0;$i<strlen($k);$i++) $q.=($i+1==strlen($k)&&$i%2==0)?$k[$i]:($i%2==0?$k[$i+1]:$k[$i-1]);
            return [substr($q,0,strpos($q,'|')),substr(basename($r), 0, strpos(basename($r),'.'))];
        }, glob($r));
    }

    public function getCategoryPageUrlExtension() {
        return $this->scopeConfig->getValue(static::CATEGORY_PAGE_URL_EXTENSION,
            ScopeInterface::SCOPE_STORE, $this->storeManager->getStore()->getId());
    }

    public function getCmsHomePage() {
        return $this->scopeConfig->getValue(static::CMS_HOME_PAGE);
    }

    public function getHomePageType() {
        return $this->scopeConfig->getValue(static::HOME_PAGE_TYPE);
    }

}