<?php
/**
 * Created by PhpStorm.
 * User: UTILISATEUR
 * Date: 28/12/2018
 * Time: 16:20
 */

namespace Floaush\Bundle\BackendGenerator\Command;


use Floaush\Bundle\BackendGenerator\Command\Interfaces\CommandMessageStatusInterface;
use Floaush\Bundle\BackendGenerator\Command\Traits\CommandHelperTrait;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ChoiceQuestion;
use Symfony\Component\Console\Question\ConfirmationQuestion;
use Symfony\Component\Process\Process;

/**
 * Class ConfigureDatabaseCommand
 * @package Floaush\Bundle\BackendGenerator\Command
 */
class ConfigureDatabaseCommand extends ContainerAwareCommand
{
    const PROCESS_EXECUTION_TIMEOUT = 86400;

    use CommandHelperTrait;

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
        $this->writeLine(
            $output,
            'Beginning of database configuration.',
            CommandMessageStatusInterface::INFO_MESSAGE_STATUS
        );

        $configurationDesired = $this->getConfigurationDesired(
            $input,
            $output
        );

        $orm = $configurationDesired['orm'];
        $rbdms = $configurationDesired['rbdms'];

        $this->writeLine(
            $output,
            "Let's configure your database to work with '" . $orm . "' and '" . $rbdms . "'.",
            CommandMessageStatusInterface::INFO_MESSAGE_STATUS
        );

        $this->configureOrm(
            $input,
            $output,
            $orm
        );
    }

    /**
     * Asks the developer some question to know which ORM and RDBMS he wants to use.
     *
     * @param \Symfony\Component\Console\Input\InputInterface   $input
     * @param \Symfony\Component\Console\Output\OutputInterface $output
     *
     * @return array
     */
    private function getConfigurationDesired(
        InputInterface $input,
        OutputInterface $output
    ) {
        /**
         * @var \Symfony\Component\Console\Helper\SymfonyQuestionHelper $questionHelper
         */
        $questionHelper = $this->getHelper('question');

        $questions = [
            'orm' => [
                'question' => 'Which ORM do you want to use for your project ?',
                'choices' => [
                    'Doctrine'
                ],
                'defaultAnswer' => 0,
                'answer' => ''
            ],
            'rbdms' => [
                'question' => 'Which RBDMS do you want to use for your project ?',
                'choices' => [
                    'MySQL',
                    'PostgreSQL'
                ],
                'defaultAnswer' => 0,
                'answer' => ''
            ]
        ];

        foreach ($questions as $subject => $question)  {
            $questions[$subject]['answer'] = $questionHelper->ask(
                $input,
                $output,
                new ChoiceQuestion(
                    $question['question'],
                    $question['choices'],
                    $question['defaultAnswer']
                )
            );
        }

        return [
            'orm' => $questions['orm']['answer'],
            'rbdms' => $questions['rbdms']['answer']
        ];
    }

    /**
     * @param \Symfony\Component\Console\Input\InputInterface   $input
     * @param \Symfony\Component\Console\Output\OutputInterface $output
     * @param string                                            $orm
     *
     * @throws \Exception
     */
    private function configureOrm(
        InputInterface $input,
        OutputInterface $output,
        string $orm
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
            $this->writeLine(
                $output,
                $orm . " ORM is found in the already registered bundles. Everything is good !",
                CommandMessageStatusInterface::INFO_MESSAGE_STATUS
            );
//            return;
        }

        $this->writeLine(
            $output,
            $orm . " ORM is not found in the already registered bundles.",
            CommandMessageStatusInterface::INFO_MESSAGE_STATUS
        );

        /**
         * @var \Symfony\Component\Console\Helper\SymfonyQuestionHelper $questionHelper
         */
        $questionHelper = $this->getHelper('question');

        $question = new ConfirmationQuestion(
            'Do you want to install the Doctrine ORM Bundle ?',
            true
        );

        $choice = $questionHelper->ask(
            $input,
            $output,
            $question
        );

        if ($choice === false) {
            $this->writeNegativeMessage(
                $output,
                'Command execution aborted. You want to use ' . $orm . ' ORM but you do not want to install it'
            );
            return;
        }

        $this->writeInformationMessage(
            $output,
            'Installation of ' . $orm . ' ORM in progress !'
        );

        $process = new Process('composer require symfony/orm-pack');
        $process->setTimeout(self::PROCESS_EXECUTION_TIMEOUT);
        $process->start();

        foreach ($process as $type => $data) {
            if ($process::OUT === $type) {
                echo 'Process : ' . $data;
            } else { // $process::ERR === $type
                echo $data;
            }
        }

        if ($process->isSuccessful()) {
            echo '[OK] - La commande a été traité avec succès !';
        } else {
            echo '[ERROR] - La commande a echoué.';
        }

        $this->cacheClear(
            $this,
            $output
        );
    }
}
