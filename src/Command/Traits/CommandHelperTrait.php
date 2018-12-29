<?php

namespace Floaush\Bundle\BackendGenerator\Command\Traits;

use Floaush\Bundle\BackendGenerator\Command\Interfaces\CommandMessageStatusInterface;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Trait CommandHelperTrait
 * @package Floaush\Bundle\BackendGenerator\Command\Traits
 */
trait CommandHelperTrait
{
    /**
     * Write some lines at the beginning of the command.
     *
     * @param \Symfony\Component\Console\Output\OutputInterface $output
     * @param string                                            $line
     * @param string                                            $status
     */
    public function writeLine (
        OutputInterface $output,
        string $line,
        string $status = 'INFO'
    ) {
        $this->generateEqualLine(
            $output,
            strlen($line)
        );
        $this->writeStatusLine(
            $output,
            $line,
            $status
        );
        $this->generateEqualLine(
            $output,
            strlen($line)
        );
    }

    /**
     * Generate a line of equal signs depending of the main line's length
     *
     * @param \Symfony\Component\Console\Output\OutputInterface $output
     * @param int                                               $lineLength
     *
     * @return mixed
     */
    public function generateEqualLine(
        OutputInterface $output,
        int $lineLength
    ) {
        $equalLine = str_repeat("=", $lineLength + 10);

        return $output->writeln($equalLine);
    }

    /**
     * Write a line with a given status and a message.
     *
     * @param \Symfony\Component\Console\Output\OutputInterface $output
     * @param string                                            $line
     * @param string                                            $status
     *
     * @return mixed
     */
    public function writeStatusLine(
        OutputInterface $output,
        string $line,
        string $status
    ) {
        return $output->writeln(
        '[' . $status . '] - ' . $line
        );
    }

    /**
     * Writes a negative message to the console output
     *
     * @param \Symfony\Component\Console\Output\OutputInterface $output
     * @param                                                   $line
     */
    private function writeNegativeMessage(
        OutputInterface $output,
        $line
    ) {
        $this->writeLine(
            $output,
            $line,
            CommandMessageStatusInterface::ERROR_MESSAGE_STATUS
        );
    }

    /**
     * Writes an information message to the console output
     *
     * @param \Symfony\Component\Console\Output\OutputInterface $output
     * @param                                                   $line
     */
    private function writeInformationMessage(
        OutputInterface $output,
        $line
    ) {
        $this->writeLine(
            $output,
            $line,
            CommandMessageStatusInterface::INFO_MESSAGE_STATUS
        );
    }

    /**
     * @param \Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand $command
     * @param \Symfony\Component\Console\Output\OutputInterface             $output
     *
     * @throws \Exception
     */
    private function cacheClear(
        ContainerAwareCommand $command,
        OutputInterface $output
    ) {
        $command = $command->getApplication()->find('cache:clear');

        $arguments = array(
            'command' => 'cache:clear'
        );

        $greetInput = new ArrayInput($arguments);
        $returnCode = $command->run($greetInput, $output);

        var_dump($returnCode); die;
    }
}
