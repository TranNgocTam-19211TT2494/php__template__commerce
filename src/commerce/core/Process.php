<?php
namespace Commerce\Core;

class Process
{
    private $_pid = null;
    private $_command = null;

    public function __construct($cl = false)
    {
        if ($cl != false) {
            $this->_command = escapeshellcmd($cl);
        }
    }

    private function _run()
    {
        $command = 'nohup ' . $this->_command . ' > /dev/null 2>&1 & echo $!';
        exec($command, $op);
        $this->_pid = (int)$op[0];
    }

    public function set_pid($pid)
    {
        $this->_pid = $pid;
    }

    public function get_pid()
    {
        return $this->_pid;
    }

    public function status()
    {
        if (!$this->_pid) {
            return false;
        }
        $command = 'ps -p ' . $this->_pid;
        exec($command, $op);
        if (!isset($op[1])) {
            return false;
        }
        return true;
    }

    public function start()
    {
        if ($this->_command) {
            $this->_run();
        }
        if ($this->status() === false) {
            return false;
        }
        return true;
    }

    public function stop()
    {
        $command = 'kill ' . $this->_pid;
        exec($command);
        if ($this->status() === false) {
            return true;
        }
        return false;
    }
}