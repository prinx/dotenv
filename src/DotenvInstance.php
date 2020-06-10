<?php
namespace Prinx\Dotenv;

require_once 'Dotenv.php';

use Prinx\Dotenv\Dotenv;

class DotenvInstance
{
    protected static $env_instance = null;

    public static function get($path = null)
    {
        // $self = new self();
        if (self::$env_instance === null) {
            self::$env_instance = new Dotenv($path);
        }

        return self::$env_instance;
    }
}
