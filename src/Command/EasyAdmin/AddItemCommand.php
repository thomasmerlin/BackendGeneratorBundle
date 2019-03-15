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
use Symfony\Component\HttpKernel\KernelInterface;
use Symfony\Component\Yaml\Yaml;

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

        $this->checkRequirements($symfonyStyle);
        $this->addBackofficeItem($symfonyStyle);
    }

    /**
     * Check if all the requirements are "OK" to execute the command.
     *
     * @param \Symfony\Component\Console\Style\SymfonyStyle $symfonyStyle
     */
    private function checkRequirements(SymfonyStyle $symfonyStyle)
    {
        $this->isBundleInstalled(
            $this->kernel,
            $symfonyStyle,
            ConstantHelper::EASY_ADMIN_BUNDLE_NAME
        );
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
        $projectDirectory = $this->kernel->getProjectDir();
        $entity = $this->getEntityClassDesired(
            $symfonyStyle,
            $projectDirectory
        );

        $this->generateEntityBackofficeConfiguration(
            $symfonyStyle,
            $projectDirectory,
            $entity
        );
    }

    /**
     * Get the entity class desired to be added in Easy Admin backoffice.
     *
     * @param \Symfony\Component\Console\Style\SymfonyStyle             $symfonyStyle
     * @param string $projectDirectory
     *
     * @return array --> An array containing the namespace and the name of the class desired to be implemented into
     * EasyAdmin
     *
     * @throws \Doctrine\ORM\ORMException
     */
    private function getEntityClassDesired(
        SymfonyStyle $symfonyStyle,
        string $projectDirectory
    ): array {
        $entityManager = $this->entityManager;
        $entitiesName = $this->getEntitiesList($entityManager);

        if (count($entitiesName) === 0) {
            $symfonyStyle->error(
                "There is no entity registered in your Symfony Application."
            );
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

        $entityInfos = [
            'class' => null,
            'namespace' => null
        ];

        foreach ($entitiesClassName as $entityClassName) {
            if ($this->getClassNameWithoutPath($entityClassName) === $entity) {
                $entityInfos['class'] = $entity;
                $entityInfos['namespace'] = $entityClassName;
                break;
            }
        }

        $this->checkIfEntityIsInBackoffice(
            $symfonyStyle,
            $projectDirectory,
            $entityInfos['class']
        );

        return $entityInfos;
    }

    /**
     * Check if the entity is registered in Easy Admin configuration
     * @see ConfigManager
     *
     * @param \Symfony\Component\Console\Style\SymfonyStyle $symfonyStyle
     * @param string                                        $projectDirectory
     * @param string                                        $entity
     */
    private function checkIfEntityIsInBackoffice(
        SymfonyStyle $symfonyStyle,
        string $projectDirectory,
        string $entity
    ) {
        $backofficeConfiguration = $this->easyAdminConfigManager->getBackendConfig();

        foreach ($backofficeConfiguration['entities'] as $entityName => $entityConfiguration) {
            if ($entity === $entityName) {
                $symfonyStyle->error(
                    'Entity "' . $entity . '" is already in the list of registered entities. You can edit her configuration in "' . $projectDirectory . '/' .
                    ConstantHelper::EASY_ADMIN_BUNDLE_CONFIGURATION_FOLDER . '/entities/' .
                    strtolower($entity) . '.yaml".'
                );
                exit();
            }
        }
    }

    /**
     * @param \Symfony\Component\Console\Style\SymfonyStyle $symfonyStyle
     * @param string $projectDirectory
     * @param array $entity
     */
    private function generateEntityBackofficeConfiguration(
        SymfonyStyle $symfonyStyle,
        string $projectDirectory,
        array $entity
    ) {
        $yamlContent = [
            'easy_admin' => [
                'entities' => [
                    $entity['class'] => [
                        'class' => $entity['namespace'],
                        'title' => $entity['class']
                    ]
                ]
            ]
        ];

        $yaml = Yaml::dump(
            $yamlContent,
            $this->getArrayMaxDepth($yamlContent)
        );

        $this->generateFile(
            $symfonyStyle,
            $projectDirectory,
            '/config/packages/easy_admin/entities',
            strtolower($entity['class']) . '.yaml',
            $yaml
        );
    }
}
