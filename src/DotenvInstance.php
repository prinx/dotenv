<?php
namespace Prinx\Dotenv;

require_once 'Dotenv.php';

use Prinx\Dotenv\Dotenv;

class DotenvInstance
{
    protected static $env_instance = null;

    public static function get()
    {
        if (!self::$env_instance) {
            self::load();
        }

        return self::$env_instance;
    }

    public static function load($path = null)
    {
        self::$env_instance = new Dotenv($path);
    }
}
