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
    protected $path = '';
    protected $pattern = '/^[^#;][a-zA-Z0-9_]+[ ]*=*[ ]*.*/';

    public function __construct($path = '')
    {
        $path = $path ?: realpath(__DIR__ . '/../../../../.env');
        $this->setPath($path);
        $this->env = \parse_ini_file($this->path, true, INI_SCANNER_TYPED);

        $this->replaceReferences();
    }

    public function replaceReferences()
    {
        foreach ($this->env as $name => $value) {
            $matches = [];
            $pattern = '/\$[^$#;][a-zA-Z0-9_]+[ ]*/';

            if (preg_match($pattern, $value, $matches)) {
                foreach ($matches as $match) {
                    if (strpos($match, '$$') === 0) {
                        continue;
                    }

                    if (!$this->envVariableExists($match)) {
                        throw new \Exception('Error in the .env file (' . $this->file . ")\n$" . $match . ' does not refer to any value');
                    }

                    $match = \substr($match, 1, strlen($match) - 1);

                    $value = preg_replace($pattern, $this->env[$match], $value);
                    $this->env[$name] = $value;
                }
            }
        }
    }

    public function get($name, $default = null)
    {
        $name_exploded = explode('.', $name);
        $lookup = $this->env;
        $value = null;

        foreach ($name_exploded as $variable_name) {
            $name_exists_in_env = isset($lookup[$name]);

            if (!$name_exists_in_env && \func_num_args() < 2) {
                throw new \Exception('Variable "' . $name . '" not defined in the .env file. You can either add the variable to the .env file or pass a second value to the function that will be return if the variable is not define in the .env file.');
            }

            $value = $lookup[$name];
            $lookup = $lookup[$name];
        }

        return $name_exists_in_env ? $value : $default;
    }

    /**
     * Section not yet support
     */
    public function add($name, $value, $section = '')
    {
        $this->env[$name] = $value;
    }

    /**
     * Write a new environment variable to the .env file
     *
     * @param string $name
     * @param string $value
     * @param boolean $overwrite If true, overwrite the variable if it was already in the file
     * @return void
     */
    public function persist($name, $value, $overwrite = true)
    {
        $line = $name . "=" . $value;
        $env_variable_exists = $this->envVariableExists($line);

        if ($env_variable_exists) {
            return $overwrite ? preg_replace($this->line_pattern, $line) : null;
        }

        return file_put_contents($this->path, "\n\n" . $line, FILE_APPEND);
    }

    public function envVariableExists($name)
    {
        return isset($this->data[$name]);
    }

    // https://stackoverflow.com/questions/9721952/search-string-and-return-line-php
    public function getLineWithString($fileName, $str)
    {
        $lines = file($fileName);
        foreach ($lines as $lineNumber => $line) {
            if (strpos($line, $str) !== false) {
                return $line;
            }
        }
        return -1;
    }

    // https://stackoverflow.com/questions/3004041/how-to-replace-a-particular-line-in-a-text-file-using-php

    public function addIfNotExists($name, $value, $section = '')
    {
        if (!isset($this->env[$name])) {
            $this->add($name, $value, $section);
        }
    }

    public function setPath($path)
    {
        if (!\file_exists($path)) {
            throw new \Exception('Trying to set the env file path but the file ' . $path . ' seems not to exist.');
        }

        $this->path = $path;
    }
}
