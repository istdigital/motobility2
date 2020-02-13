<?php

namespace TemplateMonster\SampleDataInstaller\Model\Source;

use Magento\Framework\Component\ComponentRegistrarInterface;
use Magento\Framework\Filesystem\Directory\ReadFactory;
use Magento\Framework\Option\ArrayInterface;

class Samples implements ArrayInterface
{
    /**
     * @var ComponentRegistrarInterface
     */
    protected $_componentRegistrar;

    /**
     * @var ReadFactory
     */
    protected $_readFactory;

    /**
     * @var string
     */
    protected $_directory;

    /**
     * @var string
     */
    protected $_pattern;

    /**
     * Samples constructor.
     *
     * @param ComponentRegistrarInterface $componentRegistrar
     * @param ReadFactory                 $readFactory
     * @param string                      $directory
     * @param string                      $pattern
     */
    public function __construct(
        ComponentRegistrarInterface $componentRegistrar,
        ReadFactory $readFactory,
        $directory,
        $pattern = '*.csv'
    )
    {
        $this->_componentRegistrar = $componentRegistrar;
        $this->_readFactory = $readFactory;
        $this->_directory = $directory;
        $this->_pattern = $pattern;
    }

    /**
     * @return array
     */
    public function toOptionArray()
    {
        $themes = $this->_componentRegistrar->getPaths('theme');

        $items = [];
        foreach ($themes as $name => $path) {
            $reader = $this->_readFactory->create($path . DIRECTORY_SEPARATOR . 'samples');
            if (!($files = $reader->search($this->_pattern, $this->_directory))) {
                continue;
            }
            $items[$name] = [
                'label' => $name,
                'value' => [],
            ];
            foreach ($files as $file) {
                $items[$name]['value'][] = [
                    'label' => $this->_getSampleName($file),
                    'value' => $reader->getAbsolutePath($file)
                ];
            }

        }

        return $items;
    }

    /**
     * Get sample name.
     *
     * @param string $file
     *
     * @return string
     */
    protected function _getSampleName($file)
    {
        return ucwords(str_replace('_', ' ', pathinfo($file, PATHINFO_FILENAME)));
    }
}