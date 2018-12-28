<?php

namespace Floaush\Bundle\BackendGenerator\Command\Traits;

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
     * @param string $status
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
}
