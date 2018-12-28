<?php

namespace Floaush\Bundle\BackendGeneratorBundle\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Class HelloWorldGeneratorCommand
 * @package Floaush\Bundle\BackendGeneratorBundle\Command
 */
class HelloWorldGeneratorCommand extends Command
{
    /**
     *
     */
    protected function configure()
    {
        $this
            ->setName('floaush:configure:database')
            ->setDescription('Configure the database to be fully installed afterwards without effort.')
        ;
    }

    /**
     * @param \Symfony\Component\Console\Input\InputInterface   $input
     * @param \Symfony\Component\Console\Output\OutputInterface $output
     *
     * @return int|null|void
     */
    protected function execute(
        InputInterface $input,
        OutputInterface $output
    ) {
        $output->writeln('Hello World !');
    }
}
