<?php
/**
 * Created by PhpStorm.
 * User: UTILISATEUR
 * Date: 28/12/2018
 * Time: 16:20
 */

namespace Floaush\Bundle\BackendGenerator\Command;


use Floaush\Bundle\BackendGenerator\Command\Helper\CommandMessageStatusInterface;
use Floaush\Bundle\BackendGenerator\Command\Traits\CommandHelperTrait;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ChoiceQuestion;

/**
 * Class ConfigureDatabaseCommand
 * @package Floaush\Bundle\BackendGenerator\Command
 */
class ConfigureDatabaseCommand extends Command implements CommandMessageStatusInterface
{
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

        $this->writeLine(
            $output,
            "Let's configure your database to work with '" . $configurationDesired['orm'] .
            "' and '" . $configurationDesired['rbdms'] . "'.",
            CommandMessageStatusInterface::INFO_MESSAGE_STATUS
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
}
