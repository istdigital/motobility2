<?php
/**
 * @copyright   Copyright (c) http://www.manadev.com
 * @license     http://www.manadev.com/license  Proprietary License
 */

namespace Manadev\Core\ConsoleCommands;

use Magento\Framework\Filesystem;
use Manadev\Core\Features;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class ExtensionStatusUpdateCommand extends Command
{
    /**
     * @var Filesystem
     */
    protected $filesystem;
    /**
     * @var Features
     */
    protected $features;

    public function __construct(Features $features, Filesystem $filesystem, $name = null) {
        $this->filesystem = $filesystem;
        parent::__construct($name);
        $this->features = $features;
    }

    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this->setName('mana:update')
            ->setDescription("Enables/disables modules as configured in Installed MANAdev Extensions menu.")
            ->addOption('di-compile', 'c', InputOption::VALUE_NONE, 'Run setup:di:compile afterwards')
            ->addOption('static-content-deploy', 'd', InputOption::VALUE_NONE, 'Run setup:static-content:deploy afterwards');

        parent::configure();
    }

    protected function execute(InputInterface $input, OutputInterface $output) {
        $modules = $this->features->getModulesToBeDisabledOrEnabled();
        if (!count($modules)) {
            $output->writeln("<info>There are no modules to be disabled or enabled.</info>");
            return;
        }
        
        $modulesToBeEnabled = array_keys(array_filter($modules, function ($s) { return $s;}));
        $modulesToBeDisabled = array_keys(array_filter($modules, function ($s) { return !$s;}));

        if (count($modulesToBeDisabled)) {
            $this->call('module:disable', [
                'module' => $modulesToBeDisabled,
                '--clear-static-content' => true,
            ], $output);
        }
        if (count($modulesToBeEnabled)) {
            $this->call('module:enable', [
                'module' => $modulesToBeEnabled,
                '--clear-static-content' => true,
            ], $output);
            $this->call('setup:upgrade', [], $output);
        }
        if ($input->getOption('di-compile')) {
            $this->call('setup:di:compile', [], $output);
        }
        if ($input->getOption('static-content-deploy')) {
            $this->call('setup:static-content:deploy', [], $output);
        }
    }

    protected function call($name, $arguments, OutputInterface $output) {
        $command = $this->getApplication()->find($name);
        $arguments = array_merge(['command' => $name], $arguments);
        return $command->run(new ArrayInput($arguments), $output);
    }
}