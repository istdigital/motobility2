<?php
/** 
 * @copyright   Copyright (c) http://www.manadev.com
 * @license     http://www.manadev.com/license  Proprietary License
 */

namespace Manadev\Core;

use Magento\Framework\ObjectManagerInterface;

class LogicLogger {
    /**
     * @var string
     */
    protected $file;
    /**
     * @var string
     */
    protected $filename;
    /**
     * @var bool
     */
    protected $enabled;
    /**
     * @var Logger
     */
    protected $logger;
    /**
     * @var int
     */
    protected $indent = 0;

    public function __construct($file, $enabled) {
        $this->file = $file;
        $this->enabled = $enabled;
    }

    public function enter(...$args) {
        if (!$this->enabled) {
            return;
        }

        $this->log(...$args);
        $this->indent++;
    }

    public function log(...$args) {
        if (!$this->enabled) {
            return;
        }
        if (!count($args)) {
            return;
        }

        $this->getLogger()->debug(str_repeat(' ', $this->indent * 2) . sprintf(...$args),
            ['file' => $this->getFileName()]);
    }

    public function leave(...$args) {
        if (!$this->enabled) {
            return;
        }

        $this->log(...$args);
        $this->indent--;
    }

    protected function getFileName() {
        if (!$this->filename) {
            $this->filename = $base = sprintf("%s/%s-%s",
                $this->file,
                PHP_SAPI !== 'cli' ? $_SERVER['REMOTE_ADDR'] : 'cli',
                date("Y-m-d-H-i-s")
            );

            for ($i = 2; ; $i++) {
                $filename = BP . '/var/log/mana/' . $this->filename . '.xml';
                if (!file_exists($filename)) {
                    break;
                }
                $this->filename = $base . '-' . $i;
            }
        }

        return $this->filename;
    }

    protected function getLogger() {
        if (!$this->logger) {
            $this->logger = $this->getObjectManager()->get(Logger::class);
        }

        return $this->logger;
    }

    /**
     * @return ObjectManagerInterface
     */
    protected function getObjectManager() {
        return $GLOBALS['bootstrap']->getObjectManager();
    }
}