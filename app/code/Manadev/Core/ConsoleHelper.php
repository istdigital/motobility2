<?php
/**
 * @copyright   Copyright (c) http://www.manadev.com
 * @license     http://www.manadev.com/license  Proprietary License
 */

namespace Manadev\Core;

use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Application;
use Symfony\Component\Process\Process;

class ConsoleHelper
{
    public function call($name, $arguments, Application $application, OutputInterface $output) {
        $command = $application->find($name);
        $arguments = array_merge(['command' => $name], $arguments);
        return $command->run(new ArrayInput($arguments), $output);
    }

    public function run($command) {
        $process = new Process($command, null, null, null, null);
        $process->run(function ($type, $buffer) {
            echo $buffer;
        });
    }
}