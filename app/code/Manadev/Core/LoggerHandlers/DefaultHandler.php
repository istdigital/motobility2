<?php
/** 
 * @copyright   Copyright (c) http://www.manadev.com
 * @license     http://www.manadev.com/license  Proprietary License
 */

namespace Manadev\Core\LoggerHandlers;

use Monolog\Logger;
use Magento\Framework\Logger\Handler\Base;
use Magento\Framework\Filesystem\DriverInterface;
use Monolog\Formatter\LineFormatter;

class DefaultHandler extends Base {
    /**
     * @var string
     */
    protected $fileName = '/var/log/mana.log';

    /**
     * @var int
     */
    protected $loggerType = Logger::DEBUG;

    /**
     * @param DriverInterface $filesystem
     * @param string $filePath
     */
    public function __construct(DriverInterface $filesystem, $filePath = null) {
        parent::__construct($filesystem, $filePath);
        $this->pushProcessor(function($record) {
            if (isset($record['context']['file'])) {
                $record['file'] = $record['context']['file'];
                unset($record['context']['file']);
            }
            return $record;
        });
        $this->setFormatter(new LineFormatter("%datetime%: %message%\n", null, true));
    }

    public function __destruct()
    {
        try {
            $this->close();
        } catch (\Exception $e) {
            // do nothing
        } catch (\Throwable $e) {
            // do nothing
        }
    }

    public function write(array $record) {
        if (isset($record['file'])) {
            $url = empty($record['file'])
                ? BP . '/var/log/mana.log'
                : BP . '/var/log/mana/' . $record['file'] . '.log';

            unset($record['file']);
        }
        else {
            $url = BP . '/var/log/mana.log';
        }

        if ($this->url != $url) {
            $this->close();
            $this->url = $url;
        }

        parent::write($record);
    }
}