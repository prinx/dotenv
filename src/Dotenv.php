<?php

/*
 * (c) Nuna Akpaglo <princedorcis@gmail.com>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Prinx\Dotenv;

/**
 * Class representing the environment variables.
 */
class Dotenv
{
    protected $env = [];
    protected $envFromFile = [];
    protected $path = '';

    /**
     * @var SpecialValue
     */
    protected $specialValue;

    public function __construct($path = '')
    {
        $this->setPath($path ?: realpath(__DIR__.'/../../../../.env'));

        if (file_exists($this->path)) {
            $this->envFromFile = parse_ini_file($this->path, false, INI_SCANNER_RAW);
        }

        $this->env = array_merge($_ENV, getenv(), $this->envFromFile);
    }

    public function specialValue()
    {
        if (is_null($this->specialValue)) {
            $this->specialValue = new SpecialValue();
        }

        return $this->specialValue;
    }

    public function convertSpecials()
    {
        foreach ($this->env as $name => $value) {
            if ($this->specialValue()->confirm($value)) {
                $this->env[$name] = $this->specialValue()->convert($value);
            }
        }
    }

    public function isSpecial($value)
    {
        return $this->specialValue()->confirm($value);
    }

    /**
     * Returns all the environment variables.
     */
    public function all(): array
    {
        $this->convertSpecials();
        $this->replaceAllReferences();

        return $this->env;
    }

    /**
     * Get a specific environment variable.
     *
     * @param mixed $default
     *
     * @return mixed
     */
    public function get(string $name = '', $default = null)
    {
        if (\func_num_args() === 0) {
            return $this->all();
        }

        if (array_key_exists($name, $this->env)) {
            $value = $this->formatIfContainsReference($name, $this->env[$name]);

            return $this->specialValue()->convert($value);
        }

        return $default;
    }

    /**
     * Add an environment variables to the currently loaded environment variables.
     *
     * @param string|int|bool $value
     *
     * @return $this
     */
    public function add(string $name, $value)
    {
        $this->env[$name] = $value;

        return $this;
    }

    /**
     * Write a new environment variable to the .env file.
     *
     * @param mixed $value
     *
     * @return $this
     */
    public function persist(string $name, $value)
    {
        if (!file_exists($this->path)) {
            throw new \RuntimeException('No env file found'.($this->path ? ' at '.$this->path : ''));
        }

        $pattern = '/'.$name.'[ ]*=.*/';
        $content = \file_get_contents($this->path);
        $envVariableExistsInFile = preg_match($pattern, $content);

        if ($this->isSpecial($value)) {
            $valueToWrite = $this->specialValue()->reverse($value);
            $value = $this->specialValue()->convert($value);
        } else {
            $valueToWrite = $value;
        }

        $line = $name.'='.$valueToWrite;

        // If variable in env file, just replace the value in the env, if not add a new line to env.
        if ($envVariableExistsInFile) {
            $content = preg_replace($pattern, $line, $content);
        } else {
            $content = trim($content).PHP_EOL.PHP_EOL.$line;
        }

        file_put_contents($this->path, $content);

        $this->add($name, $value);

        return $this;
    }

    public static function load($path)
    {
        return new self($path);
    }

    public function formatIfContainsReference($name, $value)
    {
        $pattern = '/\$\{([a-zA-Z0-9_]+)\}/';

        $referenceCount = preg_match_all($pattern, $value, $matches);

        while ($referenceCount) {
            foreach ($matches[1] as $key => $ref) {
                if ($ref === $name) {
                    throw new \LogicException('Cannot reference to the same variable.');
                }

                $refValue = $this->env[$ref] ?? null;
                $refValue = $this->specialValue()->reverse($refValue);

                // $fullMatch = '${'.$ref.'}';
                $fullMatch = $matches[0][$key];
                if ($fullMatch === $value) {
                    return $refValue;
                }

                var_dump($value);
                $value = str_replace($fullMatch, $refValue, $value);
                var_dump($value);

            }

            $referenceCount = preg_match_all($pattern, $value, $matches);
        }

        return $value;
    }

    /**
     * Replace the references in the .env by their respective value.
     *
     * @return $this
     */
    protected function replaceAllReferences()
    {
        foreach ($this->envFromFile as $name => $value) {            
            $this->env[$name] = $this->formatIfContainsReference($name, $value);
        }

        return $this;
    }

    public function setPath(string $path)
    {
        $this->path = $path;

        return $this;
    }

    public function getPath()
    {
        return $this->path;
    }
}
