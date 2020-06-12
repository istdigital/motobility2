<?php

namespace Zendesk\Zendesk\Logger;

use Magento\Framework\Filesystem\DriverInterface;

/**
 * This handler allows setting log file name via constructor parameter,
 * useful for virtual types.
 * This functionality exists natively in Magento 2.2, but is backported here for
 * use with Magento 2.1.
 *
 * Class Handler
 * @package Zendesk\Zendesk\Logger
 */
class Handler extends \Magento\Framework\Logger\Handler\Base
{
    /**
     * Handler constructor.
     *
     * @param DriverInterface $filesystem
     * @param string $filePath
     * @param string $fileName
     */
    public function __construct(
        DriverInterface $filesystem,
        $filePath = null,
        $fileName = null
    ) {
        // Backported from 2.2: allow setting file name via parameter
        if (!empty($fileName)) {
            $this->fileName = $fileName;
        }

        parent::__construct(
            $filesystem,
            $filePath
        );
    }
}
