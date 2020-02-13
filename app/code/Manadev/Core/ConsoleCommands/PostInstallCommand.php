<?php
/**
 * @copyright   Copyright (c) http://www.manadev.com
 * @license     http://www.manadev.com/license  Proprietary License
 */
namespace Manadev\Core\ConsoleCommands;

use Manadev\Core\Contracts\PostInstallScript;
use Manadev\Core\Registries\PostInstallScripts;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Magento\Framework\App\Config\ReinitableConfigInterface;
use Magento\Config\Model\ResourceModel\Config;

class PostInstallCommand extends Command
{

    /**
     * @var PostInstallScripts
     */
    protected $postInstallScriptRegistry;

    /**
     * @var ReinitableConfigInterface
     */
    protected $scopeConfig;
    /**
     * @var Config
     */
    protected $resourceConfig;

    public function __construct(PostInstallScripts $postInstallScriptRegistry, ReinitableConfigInterface $scopeConfig,
        Config $resourceConfig, $name = null)
    {
        $this->postInstallScriptRegistry = $postInstallScriptRegistry;
        $this->scopeConfig = $scopeConfig;
        $this->resourceConfig = $resourceConfig;
        parent::__construct($name);
    }

    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this->setName('mana:post-install')
            ->setDescription("Executes post installation code for all MANAdev Extensions.")
            ->addArgument('mage', InputArgument::OPTIONAL, 'Magento CLI command', 'php bin/magento');
    }

    protected function execute(InputInterface $input, OutputInterface $output) {
        /** @var PostInstallScript $postInstallScript */
        foreach($this->postInstallScriptRegistry->getList() as $postInstallScript) {
            $postInstallScript->execute($this->getApplication(), $output, $input->getArgument('mage'));
        }
        $this->resourceConfig->saveConfig('manadev/updated_at', '', 'default', 0);
        $this->scopeConfig->reinit();
        $output->writeln("Command `mana:post-install` successfully completed!");
    }
}