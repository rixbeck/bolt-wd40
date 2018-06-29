<?php

namespace WD40\PHPPM\Bootstraps;

use PHPPM\Bootstraps\ApplicationEnvironmentAwareInterface;
use PHPPM\Bootstraps\BootstrapInterface;
use Silex\Application;

class Silex implements BootstrapInterface, ApplicationEnvironmentAwareInterface
{
    /**
     * @var string
     */
    private $appenv;

    /**
     * @var bool
     */
    private $debug;

    /**
     * @param $appenv
     * @param $debug
     */
    public function initialize($appenv, $debug)
    {
        $this->appenv = $appenv;
        $this->debug = $debug;
        /*
         * Put ENV variable this way:
         *    putenv("CONFIG_VAR=" . $this->appenv);
         */
    }

    /**
     * @return Application
     */
    public function getApplication()
    {
        $app = require './app/bootstrap.php';

        return $app;
    }

    /**
     * @return string
     */
    public function getStaticDirectory()
    {
        return './webroot/www';
    }
}
