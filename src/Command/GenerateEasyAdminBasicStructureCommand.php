<?php

namespace Floaush\Bundle\BackendGenerator\Command;

use Floaush\Bundle\BackendGenerator\Command\Traits\EasyAdminCommandHelper;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\Yaml\Dumper;
use Symfony\Component\Yaml\Yaml;

class GenerateEasyAdminBasicStructureCommand extends ContainerAwareCommand
{
    const EASY_ADMIN_BUNDLE_NAME = 'EasyAdminBundle';
    const YAML_DUMPER_INLINE_MODE = 3;

    use EasyAdminCommandHelper;

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
        $symfonyStyle = new SymfonyStyle(
            $input,
            $output
        );

        $this->isBundleInstalled(
            $this->getContainer(),
            $symfonyStyle,
            self::EASY_ADMIN_BUNDLE_NAME
        );

        $container = $this->getContainer();

        $this->generateDesignFile(
            $symfonyStyle,
            $container
        );
        $this->generateMenuFile(
            $symfonyStyle,
            $container
        );
        $this->createEntitiesDirectory(
            $symfonyStyle,
            $container
        );
        $this->importEasyAdminResource(
            $symfonyStyle,
            $container
        );
    }

    /**
     * Generate the main design file.
     *
     * @param \Symfony\Component\Console\Style\SymfonyStyle             $symfonyStyle
     * @param \Symfony\Component\DependencyInjection\ContainerInterface $container
     */
    private function generateDesignFile(
        SymfonyStyle $symfonyStyle,
        ContainerInterface $container
    ) {
        try {
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

            $dumper = new Dumper();
            $yaml = $dumper->dump(
                $yamlContent,
                self::YAML_DUMPER_INLINE_MODE
            );

            $projectDirectory = $this->getProjectDirectory($container);

            $this->generateFile(
                $symfonyStyle,
                $projectDirectory,
                '/config/packages/easy_admin',
                'design.yaml',
                $yaml
            );
        } catch (\Exception $exception) {
            $symfonyStyle->error($exception->getMessage());
        }
    }

    /**
     * Generate the menu file.
     *
     * @param \Symfony\Component\Console\Style\SymfonyStyle             $symfonyStyle
     * @param \Symfony\Component\DependencyInjection\ContainerInterface $container
     */
    private function generateMenuFile(
        SymfonyStyle $symfonyStyle,
        ContainerInterface $container
    ) {
        try {
            $yamlContent = [
                'easy_admin' => [
                    'design' => [
                        'menu' => ['Here insert your menu elements']
                    ]
                ]
            ];

            $yaml = Yaml::dump(
                $yamlContent,
                self::YAML_DUMPER_INLINE_MODE
            );

            $projectDirectory = $this->getProjectDirectory($container);

            $this->generateFile(
                $symfonyStyle,
                $projectDirectory,
                '/config/packages/easy_admin',
                'menu.yaml',
                $yaml
            );
        } catch (\Exception $exception) {
            $symfonyStyle->error($exception->getMessage());
        }
    }

    /**
     * Create the directory to store the entities.
     *
     * @param \Symfony\Component\Console\Style\SymfonyStyle             $symfonyStyle
     * @param \Symfony\Component\DependencyInjection\ContainerInterface $container
     */
    private function createEntitiesDirectory(
        SymfonyStyle $symfonyStyle,
        ContainerInterface $container
    ) {
        $projectDirectory = $this->getProjectDirectory($container);
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
     * @param \Symfony\Component\DependencyInjection\ContainerInterface $container
     */
    private function importEasyAdminResource(
        SymfonyStyle $symfonyStyle,
        ContainerInterface $container
    ) {
        $projectDirectory = $this->getProjectDirectory($container);
        $mainEasyAdminFileExist = true;

        if (file_exists($projectDirectory . '/config/packages/easy_admin.yaml') === false) {
            $mainEasyAdminFileExist = false;
        }

        $array = [];


        if ($mainEasyAdminFileExist === true) {
            $array = Yaml::parseFile($projectDirectory . '/config/packages/easy_admin.yaml');
        }

        $array['imports']['resource'] = 'easy_admin/';

        $yaml = Yaml::dump(
            $array,
            self::YAML_DUMPER_INLINE_MODE
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
