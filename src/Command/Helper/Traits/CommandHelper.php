<?php

namespace Floaush\Bundle\BackendGenerator\Command\Helper\Traits;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpKernel\KernelInterface;
use Symfony\Component\Yaml\Yaml;

/**
 * Trait CommandHelper
 * @package Floaush\Bundle\BackendGenerator\Command\Helper\Traits
 */
trait CommandHelper
{
    /**
     * Check if a given bundle is installed.
     *
     * @param KernelInterface $kernel
     * @param SymfonyStyle $symfonyStyle
     * @param string $bundleName
     */
    private function isBundleInstalled(
        KernelInterface $kernel,
        SymfonyStyle $symfonyStyle,
        string $bundleName
    ) {
        $bundles = $kernel->getBundles();

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
     * @param KernelInterface $kernel
     *
     * @return mixed
     */
    private function getProjectDirectory(KernelInterface $kernel)
    {
        return $kernel->getRootDir();
    }

    /**
     * Initialize the Symfony Style console.
     *
     * @param \Symfony\Component\Console\Input\InputInterface   $input
     * @param \Symfony\Component\Console\Output\OutputInterface $output
     *
     * @return \Symfony\Component\Console\Style\SymfonyStyle
     */
    private function initSymfonyStyle(
        InputInterface $input,
        OutputInterface $output
    ) {
        return new SymfonyStyle(
            $input,
            $output
        );
    }

    /**
     * Get the list of the registered entities.
     *
     * @param \Doctrine\ORM\EntityManagerInterface $entityManager
     * @param boolean $cutClassNames
     *
     * @return array
     * @throws \Doctrine\ORM\ORMException
     */
    private function getEntitiesList(
        EntityManagerInterface $entityManager,
        bool $cutClassNames = true
    ) {
        $entities = [];

        $entitiesClassNames = $entityManager->getConfiguration()->getMetadataDriverImpl()->getAllClassNames();

        foreach ($entitiesClassNames as $entityClassName) {
            if ($cutClassNames === true) {
                $entityClassName = $this->getClassNameWithoutPath($entityClassName);
            }

            $entities[] = $entityClassName;
        }

        return $entities;
    }

    /**
     * Loop an array of items and list each element in a string.
     *
     * @param array $items
     *
     * @return string
     */
    private function getValidValuesfromArrayToString(array $items)
    {
        if (count($items) === 0) {
            return "";
        }

        $text = "";
        $index = 0;

        foreach ($items as $item) {
            $text .= '"' . $item . '"';
            if ($index !== (count($items) - 1)) {
                $text .= ', ';
            }

            $index++;
        }

        return $text;
    }

    /**
     * Get the directory path where are located the entities by dumping and browing into the doctrine.yaml config file.
     *
     * @param ContainerInterface $container
     *
     * @return string
     */
    private function getEntityDirectoryPath(ContainerInterface $container)
    {
        $projectDirectory = $this->getProjectDirectory($container);

        $yaml = Yaml::parseFile($projectDirectory . '/config/packages/doctrine.yaml');
        $entityPath = $yaml['doctrine']['orm']['mappings']['App']['dir'];
        $entityPathExploded = explode('/', $entityPath);

        $entityDirectoryPath = $projectDirectory;

        foreach ($entityPathExploded as $entityPathPart) {
            if ($entityPathPart !== '%kernel.project_dir%') {
                $entityDirectoryPath .= '/' . $entityPathPart;
            }
        }

        return $entityDirectoryPath;
    }

    /**
     * Get the name of the class without its path.
     *
     * @param string $fullDirectoryClassName
     *
     * @return string
     */
    private function getClassNameWithoutPath(string $fullDirectoryClassName): string
    {
        return substr(
            $fullDirectoryClassName,
            strrpos($fullDirectoryClassName, '\\') + 1
        );
    }

    /**
     * Check if the file corresponding to the given entity already exists in the backoffice.
     *
     * @param \Symfony\Component\DependencyInjection\ContainerInterface $container
     * @param string                                                    $entity
     *
     * @return bool
     */
    private function isEntityInBackoffice(
        ContainerInterface $container,
        string $entity
    ): bool {

        $projectDirectory = $this->getProjectDirectory($container);

        $filePath = $projectDirectory . '/config/packages/easy_admin/entities/' . strtolower($entity) . '.yaml';

        if (file_exists($filePath) === true) {
            return true;
        }

        return false;
    }

    /**
     * Get the array max depth.
     *
     * @param array $array
     *
     * @return int
     */
    private function getArrayMaxDepth(array $array): int
    {
        $maxDepth = 1;

        foreach ($array as $key => $value) {
            $elementDepth = 2;
            while (is_array($value) === true) {
                foreach ($value as $subValue) {
                    if (is_array($subValue) === true) {
                        $elementDepth++;
                        if ($elementDepth > $maxDepth) {
                            $maxDepth = $elementDepth;
                        }
                    }
                    $value = $subValue;
                }
            }
        }

        return $maxDepth;
    }
}