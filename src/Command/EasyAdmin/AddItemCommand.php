<?php

namespace Floaush\Bundle\BackendGenerator\Command\EasyAdmin;

use Doctrine\ORM\EntityManager;
use EasyCorp\Bundle\EasyAdminBundle\Configuration\ConfigManager;
use Floaush\Bundle\BackendGenerator\Command\Helper\ConstantHelper;
use Floaush\Bundle\BackendGenerator\Command\Helper\Traits\CommandHelper;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpKernel\KernelInterface;

/**
 * Class AddItemCommand
 * @package Floaush\Bundle\BackendGenerator\Command\EasyAdmin
 */
class AddItemCommand extends Command
{
    /**
     * Command Helper Trait
     * @see CommandHelper
     */
    use CommandHelper;

    /**
     * @var \EasyCorp\Bundle\EasyAdminBundle\Configuration\ConfigManager $easyAdminConfigManager
     */
    private $easyAdminConfigManager;

    /**
     * @var KernelInterface $kernel
     */
    private $kernel;

    /**
     * @var EntityManager $entityManager
     */
    private $entityManager;

    /**
     * AddItemCommand constructor.
     *
     * @param \EasyCorp\Bundle\EasyAdminBundle\Configuration\ConfigManager $configManager
     * @param KernelInterface $kernel
     * @param \Doctrine\ORM\EntityManager $entityManager
     */
    public function __construct(
        ConfigManager $configManager,
        KernelInterface $kernel,
        EntityManager $entityManager
    ) {
        $this->easyAdminConfigManager = $configManager;
        $this->kernel = $kernel;
        $this->entityManager = $entityManager;

        parent::__construct();
    }

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
     *
     * @throws \Doctrine\ORM\ORMException
     */
    protected function execute(
        InputInterface $input,
        OutputInterface $output
    ) {
        $symfonyStyle = $this->initSymfonyStyle(
            $input,
            $output
        );

//        var_dump($this->easyAdminConfigManager->getBackendConfig()); die;

        $this->isBundleInstalled(
            $this->kernel,
            $symfonyStyle,
            ConstantHelper::EASY_ADMIN_BUNDLE_NAME
        );

        $this->addBackofficeItem($symfonyStyle);
    }

    /**
     * Generate a new element in the backoffice.
     *
     * @param \Symfony\Component\Console\Style\SymfonyStyle             $symfonyStyle
     *
     * @throws \Doctrine\ORM\ORMException
     */
    private function addBackofficeItem(SymfonyStyle $symfonyStyle)
    {
        $entityClass = $this->getEntityClassDesired($symfonyStyle);


//        $this->generateEntityBackofficeFile(
//            $container,
//            $symfonyStyle
//        );
    }

    /**
     * Get the entity class desired to be added in Easy Admin backoffice.
     *
     * @param \Symfony\Component\Console\Style\SymfonyStyle             $symfonyStyle
     *
     * @return string --> The name of the class desired to be implemented into EasyAdmin
     *
     * @throws \Doctrine\ORM\ORMException
     */
    private function getEntityClassDesired(SymfonyStyle $symfonyStyle): string
    {
        $entityManager = $this->entityManager;
        $entitiesName = $this->getEntitiesList($entityManager);

        if (count($entitiesName) === 0) {
            $symfonyStyle->error(
                "There is no entity registered in your Symfony Application."
            );
            exit();
        }

        $entity = $symfonyStyle->ask(
            'What is the name of the entity ?',
            null,
            function ($entity) use ($entitiesName, $symfonyStyle) {
                if (in_array($entity, $entitiesName) === false) {
                    throw new \Exception(
                        'Entity "' . $entity . '" is not valid. Valid entities are : ' .
                        $this->getValidValuesfromArrayToString($entitiesName)
                    );
                }
                return $entity;
            }
        );

        $entitiesClassName = $this->getEntitiesList(
            $entityManager,
            false
        );

        $entityClass = "";

        foreach ($entitiesClassName as $entityClassName) {
            if ($this->getClassNameWithoutPath($entityClassName) === $entity) {
                $entityClass = $entityClassName;
                break;
            }
        }

        return $entityClass;
    }


    private function generateBackofficeFile(
        ContainerInterface $container,
        SymfonyStyle $symfonyStyle
    ) {

    }
}
