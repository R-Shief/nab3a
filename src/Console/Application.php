<?php

namespace App\Console;

use Symfony\Component\Console\Input\InputDefinition;
use Symfony\Component\Console\Input\InputOption;

class Application extends \Symfony\Component\Console\Application
{
    /**
     * @return InputDefinition
     * @suppress PhanTypeMismatchArgument
     */
    protected function getDefaultInputDefinition(): InputDefinition
    {
        $definition = parent::getDefaultInputDefinition();
        $definition->addOption(new InputOption('--child', null, InputOption::VALUE_NONE, 'Run as child process'));
        $definition->addOption(new InputOption('--env', '-e', InputOption::VALUE_REQUIRED, 'The environment name', 'dev'));
        $definition->addOption(new InputOption('--no-debug', null, InputOption::VALUE_NONE, 'Switches off debug mode'));

        return $definition;
    }
}
