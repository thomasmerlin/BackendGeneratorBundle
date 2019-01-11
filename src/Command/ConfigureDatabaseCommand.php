<?php
/**
 * Created by PhpStorm.
 * User: UTILISATEUR
 * Date: 28/12/2018
 * Time: 16:20
 */

namespace Floaush\Bundle\BackendGenerator\Command;

use Floaush\Bundle\BackendGenerator\Command\Traits\CommandHelperTrait;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Process\Process;

/**
 * Class ConfigureDatabaseCommand
 * @package Floaush\Bundle\BackendGenerator\Command
 */
class ConfigureDatabaseCommand extends ContainerAwareCommand
{
    const PROCESS_EXECUTION_TIMEOUT = 86400;

    /**
     *
     */
    protected function configure()
    {
        $this
            ->setName('floaush:configure:database')
            ->setDescription(
                'Configure the database to be fully installed afterwards without effort.'
            )
        ;
    }

    /**
     * @param \Symfony\Component\Console\Input\InputInterface   $input
     * @param \Symfony\Component\Console\Output\OutputInterface $output
     *
     * @return int|null|void
     * @throws \Exception
     */
    protected function execute(
        InputInterface $input,
        OutputInterface $output
    ) {
        $consoleStyling = new SymfonyStyle($input, $output);
        $consoleStyling->title("[BackOffice Generator] - Database configuration");

        $consoleStyling->section('Choosing the RBDMS & ORM configuration');
        $configurationDesired = $this->getConfigurationDesired($consoleStyling);

        $orm = $configurationDesired['orm'];
        $rbdms = $configurationDesired['rbdms'];
        $consoleStyling->success(
            'You choose "' . $orm . '" as an ORM and "' . $rbdms . '" as the RBDMS !'
        );

        $consoleStyling->section('Installing & Configuring the ORM');
        $this->configureOrm(
            $output,
            $orm,
            $consoleStyling
        );
    }

    /**
     * Asks the developer some question to know which ORM and RDBMS he wants to use.
     *
     * @param SymfonyStyle $consoleStyling
     *
     * @return array
     */
    private function getConfigurationDesired(SymfonyStyle $consoleStyling) {
        $questions = [
            'orm' => [
                'question' => 'Which ORM do you want to use for your project ?',
                'choices' => [
                    'Doctrine'
                ]
            ],
            'rbdms' => [
                'question' => 'Which RBDMS do you want to use for your project ?',
                'choices' => [
                    'MySQL',
                    'PostgreSQL'
                ]
            ]
        ];

        $configuration = [
            'orm' => null,
            'rbdms' => null
        ];

        foreach ($questions as $subject => $question)  {
            $answers = $question['choices'];
            $configuration[$subject] = $consoleStyling->choice(
                $question['question'],
                $question['choices'],
                reset($answers)
            );
        }

        $orm = $configuration['orm'];
        $rbdms = $configuration['rbdms'];

        $table = $consoleStyling->table(
            ['ORM', 'RBDMS'],
            [
                [$orm, $rbdms]
            ]
        );

        $confirmation = $consoleStyling->confirm(
            $table . ' Do you confirm this configuration ?',
            true
        );

        while ($confirmation === false) {
            $configurationDesired = $this->getConfigurationDesired($consoleStyling);

            $orm = $configurationDesired['orm'];
            $rbdms = $configurationDesired['rbdms'];

            $table = $consoleStyling->table(
                ['ORM', 'RBDMS'],
                [
                    [$orm, $rbdms]
                ]
            );

            $confirmation = $consoleStyling->confirm(
                $table . ' Do you confirm this configuration ?',
                true
            );
        }

        return $configuration;
    }

    /**
     * @param OutputInterface $output
     * @param string                                            $orm
     * @param SymfonyStyle $consoleStyling
     *
     * @throws \Exception
     */
    private function configureOrm(
        OutputInterface $output,
        string $orm,
        SymfonyStyle $consoleStyling
    ) {
        $container = $this->getContainer();
        $bundles = $container->get('kernel')->getBundles();
        $ormBundleFound = false;

        foreach ($bundles as $bundle) {
            if (preg_match('/^' . $orm . '/', $bundle->getName()) === 1) {
                $ormBundleFound = true;
            }
        }

        if ($ormBundleFound === true) {
            $consoleStyling->warning(
                'Bundle for "' . $orm . '" is already installed. Skipping to RBDMS configuration !'
            );
            return;
        }

        $consoleStyling->success(
            '"' . $orm . '" is not found in the already registered bundles.'
        );

        $consoleStyling->block(
            'Installation of "' . $orm . '" ORM in progress.'
        );

        $process = new Process('composer require orm');
        $process->setTimeout(self::PROCESS_EXECUTION_TIMEOUT);
        $process->start();

        $progressBar = new ProgressBar(
            $output,
            39
        );
        $progressBar->setFormat(
            '%current%/%max% [%bar%] %percent:3s%%  %estimated:-6s%'
        );
        $progressBar->setBarWidth($progressBar->getMaxSteps());
        $progressBar->setBarCharacter('<fg=green>⚬</>');
        $progressBar->setEmptyBarCharacter("<fg=red>⚬</>");
        $progressBar->setProgressCharacter("<fg=green>➤</>");

        foreach ($process as $type => $data) {
            $progressBar->advance();
        }

        if ($process->isSuccessful()) {
            $consoleStyling->success(
                '"' . $orm . '" a été installé avec succès !'
            );
        } else {
            $consoleStyling->error(
                '"' . $orm . '" n\'a pas pu être installé correctement.'
            );
        }
    }
}
