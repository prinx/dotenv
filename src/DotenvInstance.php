<?php
namespace Prinx\Dotenv;

require_once 'Dotenv.php';

use Prinx\Dotenv\Dotenv;

class DotenvInstance
{
    protected static $env_instance = null;

    public static function get()
    {
        // $self = new self();
        if (self::$env_instance === null) {
            self::$env_instance = new Dotenv();
        }

        return self::$env_instance;
    }
}
