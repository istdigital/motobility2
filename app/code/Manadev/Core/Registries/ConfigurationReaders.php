<?php
/**
 * @copyright   Copyright (c) http://www.manadev.com
 * @license     http://www.manadev.com/license  Proprietary License
 */

namespace Manadev\Core\Registries;

use Magento\Framework\Config\ReaderInterface;
use Magento\Framework\ObjectManagerInterface;
use Manadev\Core\Exceptions\InterfaceNotImplemented;
use Exception;

class ConfigurationReaders
{
    /**
     * @var ReaderInterface[]
     */
    protected $readers;
    /**
     * @var ObjectManagerInterface
     */
    protected $objectManager;

    public function __construct(ObjectManagerInterface $objectManager, array $readers)
    {
        $this->objectManager = $objectManager;
        $this->readers = [];

        foreach ($readers as $configName => $readerClass) {
            try {
                $reader = $this->objectManager->get($readerClass);
            }
            catch (Exception $e) {
                continue;
            }

            if ($configName != 'config' && !($reader instanceof ReaderInterface)) {
                throw new InterfaceNotImplemented(sprintf("'%s' does not implement '%s' interface.",
                    get_class($reader), ReaderInterface::class));
            }

            $this->readers[$configName] = $reader;
        }
    }

    public function get($name) {
        return isset($this->readers[$name]) ? $this->readers[$name] : false;
    }

    public function getList() {
        return $this->readers;
    }
}