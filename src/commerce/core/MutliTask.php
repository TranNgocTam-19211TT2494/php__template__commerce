<?php 
namespace Commerce\Core;

class MutliTask
{
    private $_step;
    private $_commands = [];

    public function __construct($step)
    {
        $this->_step = $step;
    }

    public function addCommand($command)
    {
        $this->_commands[] = $command;
    }

    public function execute()
    {
        $i = 1;
        $processes = [];
        foreach ($this->_commands as $command) {
            $processes[$i] = new Process($command);
            $processes[$i]->start();

            if (($i % $this->_step) == 0) {
                $is_running = true;
                while ($is_running) {
                    $still_running = false;
                    foreach ($processes as $process) {
                        if ($process->status()) {
                            $still_running = true;
                        }
                    }
                    $is_running = $still_running;
                }
                $processes = [];
            }
            $i++;
        }
        if ($processes) {
            $is_running = true;
            while ($is_running) {
                $still_running = false;
                foreach ($processes as $process) {
                    if ($process->status()) {
                        $still_running = true;
                    }
                }
                $is_running = $still_running;
            }
        }
    }
}