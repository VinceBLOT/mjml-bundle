<?php

namespace NotFloran\MjmlBundle;

use Symfony\Component\Process\Process;

final class Mjml
{
    private const VERSION_4 = 4;
    private const VERSION_BEFORE_4 = 3;

    /**
     * @var string
     */
    private $bin;

    /**
     * @var bool
     */
    private $mimify;

    /**
     * @param string $bin
     * @param bool   $mimify
     */
    public function __construct($bin, $mimify)
    {
        $this->bin = $bin;
        $this->mimify = $mimify;
    }

    /**
     * Get MJML version
     *
     * @throws \InvalidArgumentException
     *
     * @return int
     */
    private function getMjmlVersion()
    {
        $process = new Process([
            $this->bin,
            '-V',
        ]);
        $process->run();

        if (true !== $process->isSuccessful()) {
            throw new \InvalidArgumentException(sprintf(
                "Couldn't find the MJML binary"
            ));
        }

        if (strpos($process->getOutput(), 'mjml-core: 4.0') === false) {
            return self::VERSION_BEFORE_4;
        }

        return self::VERSION_4;
    }

    /**
     * @param string $mjmlContent
     * @throw \RuntimeException
     *
     * @return string
     */
    public function render($mjmlContent)
    {
        $version = $this->getMjmlVersion();

        // Tab arguments
        $arguments = [
            $this->bin,
            '-i',
            '-s',
        ];

        $strictArgument = '-l';
        if ($version === self::VERSION_4) {
            $strictArgument = '--config.validationLevel';
        }

        array_push($arguments, $strictArgument, 'strict');

        if (true === $this->mimify) {
            array_push($arguments, '-m');
        }

        // Create process
        $process = new Process($arguments);
        $process->setInput($mjmlContent);
        $process->run();

        // Executes after the command finishes
        if (true !== $process->isSuccessful()) {
            throw new \RuntimeException(sprintf(
                'The exit status code \'%s\' says something went wrong:' . "\n"
                . 'stderr: "%s"' . "\n"
                . 'stdout: "%s"' . "\n"
                . 'command: %s.',
                $process->getStatus(),
                $process->getErrorOutput(),
                $process->getOutput(),
                $process->getCommandLine()
            ));
        }

        return $process->getOutput();
    }
}
