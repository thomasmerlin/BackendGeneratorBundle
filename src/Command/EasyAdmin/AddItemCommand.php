<?php

namespace Floaush\Bundle\BackendGenerator\Command\EasyAdmin;

use Floaush\Bundle\BackendGenerator\Command\Helper\ConstantHelper;
use Floaush\Bundle\BackendGenerator\Command\Helper\Traits\CommandHelper;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Class AddItemCommand
 * @package Floaush\Bundle\BackendGenerator\Command\EasyAdmin
 */
class AddItemCommand extends ContainerAwareCommand
{
    use CommandHelper;

    /**
     * Configuration of the command.
     */
    protected function configure()
    {
        $this
            ->setName('bomaker:eab:add-item')
            ->setDescription('Add a new entity to be manageable in the backoffice.')
        ;
    }

    /**
     * Execution of the command.
     *
     * @param \Symfony\Component\Console\Input\InputInterface   $input
     * @param \Symfony\Component\Console\Output\OutputInterface $output
     *
     * @return int|null|void
     */
    protected function execute(
        InputInterface $input,
        OutputInterface $output
    ) {
        $symfonyStyle = $this->initSymfonyStyle(
            $input,
            $output
        );

        $container = $this->getContainer();

        $this->isBundleInstalled(
            $container,
            $symfonyStyle,
            ConstantHelper::EASY_ADMIN_BUNDLE_NAME
        );
    }
}
