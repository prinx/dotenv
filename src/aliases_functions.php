<?php
namespace Prinx\Dotenv;

require_once 'DotenvInstance.php';

use Prinx\Dotenv\DotenvInstance;

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

function addEnv($name, $value, $section = '')
{
    $env = DotenvInstance::get();

    \call_user_func([$env, 'set'], $name, $value, $section);
}
