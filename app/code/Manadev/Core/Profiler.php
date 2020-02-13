<?php
/**
 * @copyright   Copyright (c) http://www.manadev.com
 * @license     http://www.manadev.com/license  Proprietary License
 */

namespace Manadev\Core;

class Profiler
{
    protected $stats = [];
    protected $startedTimers = [];
    /**
     * @var Configuration
     */
    protected $configuration;

    public function __construct(Configuration $configuration) {
        $this->configuration = $configuration;
    }

    public function isEnabled() {
        return $this->configuration->isProfilerEnabled();
    }

    public function start($timerName, $tag = 'default') {
        if (!$this->isEnabled()) {
            return;
        }

        if (!isset($this->stats[$timerName])) {
            $this->stats[$timerName] = [
                'count' => 0,
                'elapsed_total' => 0,
                'elapsed_average' => 0,
                'started_at' => [],
                'tag' => $tag,
            ];
        }

        array_push($this->stats[$timerName]['started_at'], microtime(true));
        array_push($this->startedTimers, $timerName);
    }

    public function measure($timerName, $tag, callable $callback) {
        static::start($timerName, $tag);
        try {
            return $callback();
        }
        finally {
            static::stop($timerName);
        }
    }

    public function stop($timerName) {
        if (!static::isEnabled()) {
            return 0;
        }

        $elapsed = (microtime(true) - array_pop($this->stats[$timerName]['started_at'])) * 1000;

        array_pop($this->startedTimers);
        if ($startedTimerCount = count($this->startedTimers)) {
            $this->stats[$this->startedTimers[$startedTimerCount - 1]]['elapsed_total'] -= $elapsed;
        }

        $this->stats[$timerName]['elapsed_total'] += $elapsed;
        $this->stats[$timerName]['count'] += 1;
        $this->stats[$timerName]['elapsed_average'] =
            $this->stats[$timerName]['elapsed_total'] / $this->stats[$timerName]['count'];

        return $elapsed;
    }

    public function stat($elapsed, $timerName, $tag = 'default') {
        if (!static::isEnabled()) {
            return ;
        }

        if (!isset($this->stats[$timerName])) {
            $this->stats[$timerName] = [
                'count' => 0,
                'elapsed_total' => 0,
                'elapsed_average' => 0,
                'started_at' => [],
                'tag' => $tag,
            ];
        }

        if ($startedTimerCount = count($this->startedTimers)) {
            $this->stats[$this->startedTimers[$startedTimerCount - 1]]['elapsed_total'] -= $elapsed;
        }

        $this->stats[$timerName]['elapsed_total'] += $elapsed;
        $this->stats[$timerName]['count'] += 1;
        $this->stats[$timerName]['elapsed_average'] =
            $this->stats[$timerName]['elapsed_total'] / $this->stats[$timerName]['count'];
    }

    public function getStats() {
        return $this->stats;
    }
}