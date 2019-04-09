<?php

namespace Resta\Support;

use Resta\Contracts\HandleContracts;
use Resta\Foundation\ApplicationProvider;
use Symfony\Component\Process\Process as ProcessHandler;
use Symfony\Component\Process\Exception\ProcessFailedException;

class Command extends ApplicationProvider implements HandleContracts
{
    /**
     * @var $arguments
     */
    protected $arguments;

    /**
     * Command constructor.
     *
     * @param mixed ...$params
     */
    public function __construct(...$params)
    {
        [$command,$args]    = $params;

        $this->arguments[]  = 'php';
        $this->arguments[]  = 'api';
        $this->arguments    = array_merge($this->arguments,explode(" ",$command));
        $this->arguments[]  = strtolower(app);
        $this->arguments[]  = $args;
    }

    /**
     * handle application command
     *
     * @return bool
     */
    public function handle()
    {
        $process = new ProcessHandler($this->arguments,root.'');
        $process->start();

        foreach ($process as $type => $data) {
            if ($process::OUT !== $type) {
                return false;
            }
            return true;
        }
    }
}