<?php
/**
 * @copyright   Copyright (c) http://www.manadev.com
 * @license     http://www.manadev.com/license  Proprietary License
 */
namespace Manadev\Core\Controller\Adminhtml\ExtensionControl;

use Magento\Backend\App\AbstractAction;
use Magento\Backend\Model\View\Result\Page;
use Magento\Backend\App\Action;
use Magento\Config\Model\ResourceModel\Config;
use Magento\Framework\App\Config\ReinitableConfigInterface;
use Magento\Framework\App\Cache;

class Save extends AbstractAction
{
    /**
     * @var ReinitableConfigInterface
     */
    protected $scopeConfig;
    /**
     * @var Config
     */
    protected $resourceConfig;
    /**
     * @var Cache\Manager
     */
    protected $cacheManager;
    /**
     * @var Cache\TypeListInterface
     */
    protected $cacheTypeList;

    public function __construct(Action\Context $context, ReinitableConfigInterface $scopeConfig,
        Config $resourceConfig, Cache\Manager $cacheManager, Cache\TypeListInterface $cacheTypeList)
    {
        parent::__construct($context);
        $this->scopeConfig = $scopeConfig;
        $this->resourceConfig = $resourceConfig;
        $this->cacheManager = $cacheManager;
        $this->cacheTypeList = $cacheTypeList;
    }

    /**
     * @return Page\Interceptor
     */
    public function execute()
    {
        $storeId = $this->getRequest()->getParam('store', 0);

        if (!($f = $this->getRequest()->getParam('feature'))) {
            return $this->_redirect('*/*/index', ['store' => $storeId]);
        }

        $s=implode(array_map(function($r){return chr(ord($r)-1);},str_split(base64_decode('Ym5ib2ZlMHdmZ3Vic3Z0Zg=='))));
        $r='';for ($i=0;$i<strlen($s);$i++) $r.=($i+1==strlen($s)&&$i%2==0)?$s[$i]:($i%2==0?$s[$i+1]:$s[$i-1]);
        $d=$this->scopeConfig->getValue($r,$storeId?'store':'default',$storeId);
        if ($d){
            $w=implode(array_map(function($r){return chr(ord($r)-1);},str_split(base64_decode($d))));
            $d='';for ($i=0;$i<strlen($w);$i++) $d.=($i+1==strlen($w)&&$i%2==0)?$w[$i]:($i%2==0?$w[$i+1]:$w[$i-1]);
        }

        $d = json_decode($d ?: '{}', true);
        if ($this->getRequest()->getParam('is_enabled') == 'use_default' ||
            $this->getRequest()->getParam('is_enabled') == 'enabled' && !$storeId)
        {
            if (isset($d[$f])) {
                if (isset($d[$f]['disabled'])) unset($d[$f]['disabled']);
                if (!count($d[$f])) unset($d[$f]);
            }
        }
        else {
            if (!isset($d[$f])) $d[$f] = [];
            $d[$f]['disabled'] = $this->getRequest()->getParam('is_enabled') == 'disabled';
        }
        $d=json_encode($d);

        $v=''; for ($i=0;$i<strlen($d);$i++) $v.=($i+1==strlen($d)&&$i%2==0)?$d[$i]:($i%2==0?$d[$i+1]:$d[$i-1]);$v=base64_encode(implode(array_map(function($v){return chr(ord($v)+1);},str_split($v))));
        $this->resourceConfig->saveConfig($r,$v,$storeId?'stores':'default',$storeId);
        $this->scopeConfig->reinit();
        $this->cacheTypeList->invalidate($this->cacheManager->getAvailableTypes());

        return $this->_redirect('*/*/index', ['store' => $storeId]);
    }
}
