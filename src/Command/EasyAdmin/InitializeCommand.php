<?php

namespace Floaush\Bundle\BackendGenerator\Command\EasyAdmin;

use Floaush\Bundle\BackendGenerator\Command\Helper\ConstantHelper;
use Floaush\Bundle\BackendGenerator\Command\Helper\Traits\CommandHelper;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\HttpKernel\KernelInterface;
use Symfony\Component\Yaml\Yaml;

/**
 * Class InitializeCommand
 * @package Floaush\Bundle\BackendGenerator\Command\EasyAdmin
 */
class InitializeCommand extends Command
{
    use CommandHelper;

    /**
     * @var KernelInterface $kernel
     */
    private $kernel;

    /**
     * InitializeCommand constructor.
     *
     * @param \Symfony\Component\HttpKernel\KernelInterface $kernel
     */
    public function __construct(KernelInterface $kernel)
    {
        $this->kernel = $kernel;

        parent::__construct();
    }

    /**
     * Configuration of the command
     */
    protected function configure()
    {
        $this
            ->setName('bomaker:eab:init')
            ->setDescription('Generate a basic structure of Easy Admin Bundle.')
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

        $this->checkRequirements($symfonyStyle);
        $this->generateStructure($symfonyStyle);
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
     * Generates the minimal structure for Easy Admin Bundle.
     * (Note : This structure is taken from the official Easy Admin Demo Github repository.)
     * @see https://github.com/javiereguiluz/easy-admin-demo
     *
     * @param \Symfony\Component\Console\Style\SymfonyStyle $symfonyStyle
     */
    private function generateStructure(SymfonyStyle $symfonyStyle)
    {
        $projectDirectory = $this->kernel->getProjectDir();

        $this->generateDesignFile(
            $symfonyStyle,
            $projectDirectory
        );
        $this->generateMenuFile(
            $symfonyStyle,
            $projectDirectory
        );
        $this->createEntitiesDirectory(
            $symfonyStyle,
            $projectDirectory
        );
        $this->importEasyAdminResource(
            $symfonyStyle,
            $projectDirectory
        );
    }

    /**
     * Generate the main design file.
     *
     * @param \Symfony\Component\Console\Style\SymfonyStyle             $symfonyStyle
     * @param string $projectDirectory
     */
    private function generateDesignFile(
        SymfonyStyle $symfonyStyle,
        string $projectDirectory
    ) {
        $yamlContent = [
            'easy_admin' => [
                'site_name' => 'My site',
                'design' => ['brand_color' => '#00000'],
                'formats' => ['datetime' => 'd/m/Y']
            ]
        ];

        $yamlContent['easy_admin']['site_name'] = $symfonyStyle->ask(
            'What is your site name ?',
            $yamlContent['easy_admin']['site_name']
        );

        $yaml = Yaml::dump(
            $yamlContent,
            $this->getArrayMaxDepth($yamlContent)
        );

        $this->generateFile(
            $symfonyStyle,
            $projectDirectory,
            '/config/packages/easy_admin',
            'design.yaml',
            $yaml
        );
    }

    /**
     * Generate the menu file.
     *
     * @param \Symfony\Component\Console\Style\SymfonyStyle             $symfonyStyle
     * @param string $projectDirectory
     */
    private function generateMenuFile(
        SymfonyStyle $symfonyStyle,
        string $projectDirectory
    ) {
        $yamlContent = [
            'easy_admin' => [
                'design' => [
                    'menu' => ['Here insert your menu elements']
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
            '/config/packages/easy_admin',
            'menu.yaml',
            $yaml
        );
    }

    /**
     * Create the directory to store the entities.
     *
     * @param \Symfony\Component\Console\Style\SymfonyStyle             $symfonyStyle
     * @param string $projectDirectory
     */
    private function createEntitiesDirectory(
        SymfonyStyle $symfonyStyle,
        string $projectDirectory
    ) {
        $entitiesDirectory = $projectDirectory . '/config/packages/easy_admin/entities';

        if (is_dir($entitiesDirectory) === false) {
            mkdir($entitiesDirectory, 0777);
        }

        file_put_contents(
            $entitiesDirectory . '/.gitkeep',
            ''
        );

        $symfonyStyle->success(
            'Entities directory successfully created at "' . $entitiesDirectory . '".'
        );
    }

    /**
     * Import the easy admin directory to the main easy admin file
     *
     * @param \Symfony\Component\Console\Style\SymfonyStyle             $symfonyStyle
     * @param string $projectDirectory
     */
    private function importEasyAdminResource(
        SymfonyStyle $symfonyStyle,
        string $projectDirectory
    ) {
        $yamlContent = [];

        if (file_exists($projectDirectory . '/config/packages/easy_admin.yaml') === true) {
            $yamlContent = Yaml::parseFile($projectDirectory . '/config/packages/easy_admin.yaml');
        }

        $yamlContent['imports']['resource'] = 'easy_admin/';

        $yaml = Yaml::dump(
            $yamlContent,
            $this->getArrayMaxDepth($yamlContent)
        );

        file_put_contents(
            $projectDirectory . '/config/packages/easy_admin.yaml',
            $yaml
        );

        $symfonyStyle->success(
            'Easy Admin structure successfully generated. When working with EasyAdmin, please create/edit files at "'
            . $projectDirectory . '/config/packages/easy_admin" directory.'
        );
    }
}
