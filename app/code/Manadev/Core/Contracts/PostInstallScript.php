<?php
/**
 * @copyright   Copyright (c) http://www.manadev.com
 * @license     http://www.manadev.com/license  Proprietary License
 */

namespace Manadev\Core\Contracts;

use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Application;

interface PostInstallScript
{
    public function execute(Application $application, OutputInterface $output, $mage);
}