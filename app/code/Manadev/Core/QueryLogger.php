<?php
/** 
 * @copyright   Copyright (c) http://www.manadev.com
 * @license     http://www.manadev.com/license  Proprietary License
 */

namespace Manadev\Core;

use Magento\Framework\DB\LoggerInterface;

class QueryLogger {
    protected $files = [];
    protected $fileNames = [];

    /**
     * @var
     */
    protected $logger;
    /**
     * @var Configuration
     */
    protected $configuration;

    public function __construct(Logger $logger, Configuration $configuration) {
        $this->logger = $logger;
        $this->configuration = $configuration;
        //$this->begin('all');
    }

    protected function getFileName($file) {
        if (!isset($this->fileNames[$file])) {
            $this->fileNames[$file] = sprintf("%s/%s-%s",
                $file,
                PHP_SAPI !== 'cli' ? $_SERVER['REMOTE_ADDR'] : 'cli',
                date("Y-m-d-H-i-s")
            );
        }

        return $this->fileNames[$file];
    }

    public function begin($file) {
        if (isset($this->files[$file])) {
            $this->files[$file]['count']++;
        }
        else {
            $this->files[$file] = ['name' => $this->getFileName($file), 'count' => 1];
        }
    }

    public function end($file) {
        if (isset($this->files[$file])) {
            if ($this->files[$file]['count'] > 1) {
                $this->files[$file]['count']--;
            }
            else {
                unset($this->files[$file]);
            }
        }
    }

    public function log($type, $sql, $time, $bind = [], $result = null) {
        foreach ($this->files as $file) {
            $this->logToFile($file['name'], $type, $sql, $time, $bind, $result);
        }
    }

    protected function logToFile($file, $type, $sql, $time, $bind = [], $result = null) {
        if ($trace = $this->findTrace()) {
            $this->logger->debug(sprintf("%s::%s(): %s (%d rows affected)\n\n    at %s (%d)\n    %s\n\n%s\n",
                $trace['class'],
                $trace['function'],

                $trace['db_method'],
                $this->getAffectedRows($type, $result),

                $trace['file'],
                $trace['line'],

                $time,
                $this->formatSql($sql, $bind)
            ), compact('file'));

        }
        else {
            $this->logger->debug(sprintf("%d rows affected\n    %s\n\n%s\n",
                $this->getAffectedRows($type, $result),
                $time,
                $this->formatSql($sql, $bind)
            ), compact('file'));
        }
    }

    protected $dbClasses = ['Zend_Db_Adapter_Abstract', 'Magento\Framework\DB\Adapter\Pdo\Mysql'];

    protected function findTrace() {
        try {
            throw new \Exception();
        }
        catch (\Exception $e) {
            $traces = array_reverse($e->getTrace());

            foreach ($traces as $traceIndex => $trace) {
                if (!isset($trace['class']) || !in_array($trace['class'], $this->dbClasses)) {
                    continue;
                }

                return [
                    'class' => $traces[$traceIndex - 1]['class'],
                    'function' => $traces[$traceIndex - 1]['function'],
                    'db_method' => $trace['function'],
                    'file' => $trace['file'],
                    'line'=> $trace['line'],
                ];
            }
        }

        return null;
    }

    protected function formatSql($sql, $bind = []) {
        $escaped = false;
        $quote = '';
        $pos = -1;
        $result = '';

        for ($i = $pos + 1; $i < mb_strlen($sql); $i++) {
            $ch = substr($sql, $i, 1);

            if ($ch == "\\") {
                $escaped = true;
            }
            elseif ($escaped) {
                $escaped = false;
            }
            elseif ($quote) {
                if ($ch == $quote) {
                    $quote = '';
                }
            }
            elseif ($ch == "'") {
                $quote = $ch;
            }
            elseif ($ch == '?') {
                if (count($bind)) {
                    $value = array_shift($bind);
                    if ($value !== null) {
                        $value = "'$value'";
                    }
                    $result .= $value;
                    continue;
                }
            }

            $result .= $ch;
        }

        $result = str_replace("FROM", "\nFROM",
            str_replace(" SELECT", "\nSELECT",
            str_replace("WHERE", "\nWHERE",
            str_replace("ON DUPLICATE KEY", "\nON DUPLICATE KEY", $result
        ))));

        $result = implode("\n", array_map(function ($line) {
            return '    ' . $line;
        }, explode("\n", $result)));

        return $result;
    }

    protected function getAffectedRows($type, $result = null) {
        return $type == LoggerInterface::TYPE_QUERY && $result instanceof \Zend_Db_Statement_Pdo
            ? $result->rowCount()
            : 0;
    }
}