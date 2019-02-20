<?php

namespace Floaush\Bundle\BackendGenerator\Command\Traits;

use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\DependencyInjection\ContainerInterface;

trait EasyAdminCommandHelper
{
    /**
     * Check if a given bundle is installed.
     *
     * @param ContainerInterface $container
     * @param SymfonyStyle $symfonyStyle
     * @param string $bundleName
     */
    private function isBundleInstalled(
        ContainerInterface $container,
        SymfonyStyle $symfonyStyle,
        string $bundleName
    ) {
        $bundles = $container->get('kernel')->getBundles();

        /**
         * @var \Symfony\Component\HttpKernel\Bundle\BundleInterface $bundle
         */
        foreach ($bundles as $bundle) {
            if ($bundle->getName() === $bundleName) {
                $symfonyStyle->success(
                    '"' . $bundleName . '" is installed. Requirements are good.'
                );
                return;
            }
        }

        $symfonyStyle->error(
            '"' . $bundleName . '" is not installed. Please consider installing it before running the command again.'
        );
        exit();
    }

    /**
     * Generate a file and inserts a given content to it.
     * It also create the folder path if not exists.
     *
     * @param SymfonyStyle $symfonyStyle       | The Symfony Console Style
     * @param string $projectDirectory         | The path of the project root
     * @param string $targetDirectoryPath      | The path to the file to be generated
     * @param string $filename                 | The name of the file to be created
     * @param mixed|string $fileContent        | The content to insert in the file
     */
    private function generateFile(
        SymfonyStyle $symfonyStyle,
        string $projectDirectory,
        string $targetDirectoryPath,
        string $filename,
        $fileContent = ""
    ) {
        $fullDirectoryPath = $projectDirectory . $targetDirectoryPath;

        if (is_dir($fullDirectoryPath) === false) {
            mkdir($fullDirectoryPath, 0777);
        }

        file_put_contents(
            $fullDirectoryPath . '/' . $filename,
            $fileContent
        );

        $symfonyStyle->success(
            '"' . $filename . '" file has been generated correctly at "' . $fullDirectoryPath . '/' . $filename . '".'
        );
    }

    /**
     * Get the path of the project root directory.
     *
     * @param \Symfony\Component\DependencyInjection\ContainerInterface $container
     *
     * @return mixed
     */
    private function getProjectDirectory(ContainerInterface $container)
    {
        return $container->getParameter('kernel.project_dir');
    }
}