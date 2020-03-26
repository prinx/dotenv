<?php
/**
 * (c) Nuna Akpaglo <princedorcis@gmail.com>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Prinx\Dotenv;

class Dotenv
{
    protected $env = [];
    protected $path = '.env';

    public function __construct($path = '.env')
    {
        if ($path !== $this->path) {
            $this->set_path($path);
        }

        $this->env = \parse_ini_file($this->path);
    }

    public function get($name, $default = null)
    {
        $name_exists_in_env = isset($this->env[$name]);

        if (!$name_exists_in_env && \func_num_args() < 2) {
            throw new \Exception('Variable "' . $name . '" not defined in the .env file. You can either add the variable to the .env file or pass a second value to the function that will be return if the variable is not define in the .env file.');
        }

        return $name_exists_in_env ? $this->env[$name] : $default;
    }

    /**
     * Section not yet support
     */
    public function add($name, $value, $section = '')
    {
        /*
        $fp = \fopen($this->path, 'a');

        if ($fp === false) {
        throw new \Exception('Error when writing in the .env file.');
        }

        \fwrite($fp, "\n" . $name . '=' . $value);
        \fclose($fp);
         */

        $this->env[$name] = $value;
    }

    public function add_if_not_exists($name, $value, $section = '')
    {
        if (!isset($this->env[$name])) {
            $this->add($name, $value, $section);
        }
    }

    public function set_path($path)
    {
        if (!\file_exists($path)) {
            throw new \Exception('Trying to set the env file path but the file ' . $path . ' seems not to exist.');
        }

        $this->path = $path;
    }
}

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

function env($name = null, $default = null)
{
    $env = DotenvInstance::get();

    switch (\func_num_args()) {
        case 0:
            return $env;
        case 1:
            return \call_user_func([$env, 'get'], $name);

        default:
            return \call_user_func([$env, 'get'], $name, $default);
    }
}

function add_env($name, $value, $section = '')
{
    $env = DotenvInstance::get();

    \call_user_func([$env, 'set'], $name, $value, $section);
}
