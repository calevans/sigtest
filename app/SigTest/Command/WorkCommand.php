<?php

namespace SigTest\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class WorkCommand extends Command
{

    protected $continueFlag = true;
    protected $pid = null;
    protected $pidFileLocation = '../';

    protected function configure()
    {
        $this->setName('work')
             ->setDescription('Work Command. Be careful when executing!')
		     ->setHelp('Work');
    }
    
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->pid = getmypid();

        if (!$this->checkPidFile()) {
            $this->setPidFile();
        } else {
            throw new \Exception('Process already running! Aborting.');
        }

        declare(ticks = 10);
        pcntl_signal(SIGHUP, [$this, 'signalHandler']);
        pcntl_signal(SIGINT, [$this, 'signalHandler']);
        pcntl_signal(SIGUSR1, [$this, 'signalHandler']);
        pcntl_signal(SIGUSR2, [$this, 'signalHandler']);
        pcntl_signal(SIGQUIT, [$this, 'signalHandler']);
        pcntl_signal(SIGILL, [$this, 'signalHandler']);
        pcntl_signal(SIGABRT, [$this, 'signalHandler']);
        pcntl_signal(SIGFPE, [$this, 'signalHandler']);
        pcntl_signal(SIGSEGV, [$this, 'signalHandler']);
        pcntl_signal(SIGPIPE, [$this, 'signalHandler']);
        pcntl_signal(SIGALRM, [$this, 'alarmHandler']);
        pcntl_signal(SIGTERM, [$this, 'signalHandler']);
        pcntl_signal(SIGCHLD, [$this, 'signalHandler']);
        pcntl_signal(SIGCONT, [$this, 'signalHandler2']);
        pcntl_signal(SIGTSTP, [$this, 'signalHandler']);
        pcntl_signal(SIGTTIN, [$this, 'signalHandler']);
        pcntl_signal(SIGTTOU, [$this, 'signalHandler']);

        pcntl_alarm(5);

        do {
            echo ".";
            // do something interesting here.
            sleep(10);
            pcntl_signal_dispatch();
        } while ($this->continueFlag);

        $output->writeln('It Worked!');

        $this->removePidFile();
    }

    protected function checkPidFile()
    {
        $pidFile = $this->getPidFileName();
        $returnValue = file_exists($pidFile);

        return $returnValue;
    }

    protected function getPidFileName()
    {
        $pidFile = $this->getName() . '.pid';

        return $this->pidFileLocation . $pidFile;
    }

    protected function setPidFile()
    {
        $currentPid = $this->pid;
        $pidFile = $this->getPidFileName();
        file_put_contents($pidFile, $currentPid, LOCK_EX);
    }

    /**
     * Wouldn't this make a great trait? ;)
     */
    protected function removePidFile()
    {
        $pidFileName = $this->getPidFileName();
        unlink($pidFileName);
    }

    /**
     * @param int $signal
     */
    protected function signalhandler($signal)
    {

        echo "Caught a signal" . PHP_EOL;
        $this->continueFlag = false;
    }

    /**
     * @param int $signal
     */
    protected function signalhandler2($signal)
    {
        echo "Caught the continue signal" . PHP_EOL;
    }

    /**
     * @param int $signal
     */
    protected function alarmHandler($signal)
    {
        echo "ALARM!" . PHP_EOL;
        pcntl_alarm(5);
    }

}
