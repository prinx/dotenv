<?php

/**
 * (c) Nuna Akpaglo <princedorcis@gmail.com>.
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */
if (! function_exists('env') || __NAMESPACE__) {
    /**
     * Retrieve an environment variable.
     *
     * Look for an environment variables in the current .env file,
     * the $_ENV superglobal and using the built-in getenv function
     *
     * @param  string  $name
     * @param  mixed   $default
     * @return mixed
     */
    function env($key = null, $default = null)
    {
        return Prinx\Dotenv\env(...(func_get_args()));
    }
}

if (! function_exists('addEnv') || __NAMESPACE__) {
    /**
     * Add a temporary environment variable to the current loaded environment variables.
     *
     * @param  string $name
     * @param  mixed  $value
     * @return void
     */
    function addEnv($name, $value)
    {
        return Prinx\Dotenv\addEnv($name, $value);
    }
}

if (! function_exists('persistEnv') || __NAMESPACE__) {
    /**
     * Write an environment variable to the loaded .env file.
     *
     * @param  string $name
     * @param  mixed  $value
     * @param  bool   $overwrite
     * @param  bool   $quote_string
     * @return void
     */
    function persistEnv($name, $value, $overwrite = true, $quote_string = true)
    {
        return Prinx\Dotenv\persistEnv(...(func_get_args()));
    }
}

if (! function_exists('allEnv') || __NAMESPACE__) {
    /**
     * Returns all the environment variables in the .env file as an array.
     *
     * @return array
     */
    function allEnv()
    {
        return Prinx\Dotenv\allEnv();
    }
}

if (! function_exists('dotenv') || __NAMESPACE__) {
    /**
     * Returns the Dotenv instance.
     *
     * @return Dotenv
     */
    function dotenv()
    {
        return Prinx\Dotenv\dotenv();
    }
}

if (! function_exists('loadEnv') || __NAMESPACE__) {
    /**
     * Load a specific .env file.
     *
     * @param  string $path
     * @return void
     */
    function loadEnv($path = null)
    {
        return Prinx\Dotenv\loadEnv($path);
    }
}
