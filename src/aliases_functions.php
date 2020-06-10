<?php
namespace Prinx\Dotenv;

require_once 'DotenvInstance.php';

use Prinx\Dotenv\DotenvInstance;

function env($name = null, $default = null)
{
    $env = DotenvInstance::get();

    switch (\func_num_args()) {
        case 0:
            return \call_user_func([$env, 'all']);
        case 1:
            return \call_user_func([$env, 'get'], $name);

        default:
            return \call_user_func([$env, 'get'], $name, $default);
    }
}

function addEnv($name, $value)
{
    $env = DotenvInstance::get();

    \call_user_func([$env, 'add'], $name, $value);
}

function persistEnv($name, $value)
{
    $env = DotenvInstance::get();

    \call_user_func([$env, 'persist'], $name, $value);
}

function allEnv()
{
    $env = DotenvInstance::get();

    \call_user_func([$env, 'all']);
}

function dotenv()
{
    return DotenvInstance::get();
}
