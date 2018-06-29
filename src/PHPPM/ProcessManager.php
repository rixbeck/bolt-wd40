<?php
declare(ticks = 1);

namespace WD40\PHPPM;

use PHPPM\Slave;
use Symfony\Component\Process\ProcessUtils;
use React\ChildProcess\Process;

class ProcessManager extends \PHPPM\ProcessManager
{
    /**
     * @param int $port
     * @throws \Exception
     */
    protected function newSlaveInstance($port)
    {
        if ($this->status === self::STATE_SHUTDOWN) {
            // during shutdown phase all connections are closed and as result new
            // instances are created - which is forbidden during this phase
            return;
        }

        if ($this->output->isVeryVerbose()) {
            $this->output->writeln(sprintf("Start new worker #%d", $port));
        }

        $socketpath = var_export($this->getSocketPath(), true);
        $bridge = var_export($this->getBridge(), true);
        $bootstrap = var_export($this->getAppBootstrap(), true);

        $config = [
            'port' => $port,
            'session_path' => session_save_path(),

            'app-env' => $this->getAppEnv(),
            'debug' => $this->isDebug(),
            'logging' => $this->isLogging(),
            'static-directory' => $this->getStaticDirectory(),
            'populate-server-var' => $this->isPopulateServer()
        ];

        $config = var_export($config, true);

        $dir = var_export(__DIR__ . '/../..', true);
        $script = <<<EOF
<?php

namespace PHPPM;

set_time_limit(0);

require_once file_exists($dir . '/vendor/autoload.php')
    ? $dir . '/vendor/autoload.php'
    : $dir . '/../../autoload.php';
    
if (!pcntl_installed()) {
    error_log(
        sprintf(
            'PCNTL is not enabled in the PHP installation at %s. See: http://php.net/manual/en/pcntl.installation.php',
            PHP_BINARY
        )
    );
    exit();
}

if (!pcntl_enabled()) {
    error_log('Some required PCNTL functions are disabled. Check `disabled_functions` in `php.ini`.');
    exit();
}

//global for all global functions
ProcessSlave::\$slave = new ProcessSlave($socketpath, $bridge, $bootstrap, $config);
ProcessSlave::\$slave->run();
EOF;

        // slave php file
        $file = tempnam(sys_get_temp_dir(), 'dbg');
        file_put_contents($file, $script);
        register_shutdown_function('unlink', $file);

        // we can not use -q since this disables basically all header support
        // but since this is necessary at least in Symfony we can not use it.
        // e.g. headers_sent() returns always true, although wrong.
        //For version 2.x and 3.x of \Symfony\Component\Process\Process package
        if (method_exists('\Symfony\Component\Process\ProcessUtils', 'escapeArgument')) {
            $commandline = 'exec ' . $this->phpCgiExecutable . ' -C ' . ProcessUtils::escapeArgument($file);
        } else {
            //For version 4.x of \Symfony\Component\Process\Process package
            $commandline = ['exec', $this->phpCgiExecutable, '-C', $file];
            $processInstance = new \Symfony\Component\Process\Process($commandline);
            $commandline = $processInstance->getCommandLine();
        }

        // use exec to omit wrapping shell
        $process = new Process($commandline);

        $slave = new Slave($port, $this->maxRequests);
        $slave->attach($process);
        $this->slaves->add($slave);

        $process->start($this->loop);
        $process->stderr->on(
            'data',
            function ($data) use ($port) {
                if ($this->lastWorkerErrorPrintBy !== $port) {
                    $this->output->writeln("<info>--- Worker $port stderr ---</info>");
                    $this->lastWorkerErrorPrintBy = $port;
                }
                $this->output->write("<error>$data</error>");
            }
        );
    }
}
