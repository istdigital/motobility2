<?php
/**
 * @copyright   Copyright (c) http://www.manadev.com
 * @license     http://www.manadev.com/license  Proprietary License
 */

namespace Manadev\Core\Observers;

use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Manadev\Core\Profiler;
use Magento\Framework\App\Request;
use Magento\Framework\App\Response;

class OutputProfilerStats implements ObserverInterface
{
    /**
     * @var Profiler
     */
    protected $profiler;

    public function __construct(Profiler $profiler) {
        $this->profiler = $profiler;
    }

    public function execute(Observer $observer) {
        if (!$this->profiler->isEnabled()) {
            return;
        }

        /* @var Request\Http $request */
        $request = $observer->getData('request');
        if ($request->isAjax()) {
            return;
        }

        $s = "<pre>\n";
        $s .= sprintf("%20s%-60s%15s%15s%10s\n", '', 'Method', 'Total (ms)', 'Average (ms)', 'Count');
        $s .= str_repeat(' ', 20) . str_repeat('-', 60 + 15 + 15 + 10) . "\n";
        foreach ($this->profiler->getStats() as $name => $stat) {
            $s .= sprintf("%20s%-60s%15.1f%15.1f%10d\n", '', $name, $stat['elapsed_total'], $stat['elapsed_average'], $stat['count']);
        }
        $s .= "</pre>\n";

        /* @var Response\Http $response */
        $response = $observer->getData('response');
        $response->appendBody($s);
    }
}