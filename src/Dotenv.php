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
    protected $path = '';

    /**
     * @var SpecialValue
     */
    protected $specialValue;

    public function __construct($path = '')
    {
        $path = $path ?: realpath(__DIR__.'/../../../../.env');
        $this->setPath($path);

        $env = \file_exists($this->path) ? \parse_ini_file($this->path, false, INI_SCANNER_RAW) : [];
        $this->env = array_merge($_ENV, getenv(), $env);

        $this->convertSpecials();
        $this->replaceReferences();
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

        return $this->env[$name] ?? $default;
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

    /**
     * Replace the references in the .env by their respective value.
     *
     * @return $this
     */
    protected function replaceReferences()
    {
        if (!\file_exists($this->path)) {
            return $this;
        }

        $env = file($this->path, FILE_SKIP_EMPTY_LINES | FILE_IGNORE_NEW_LINES);
        $pattern = '/^([^#;][a-zA-Z0-9_]+)[ ]*=[ ]*["\']?([^"\']*\$\{([a-zA-Z0-9_]+)\}[^"\']*)["\']?/';

        foreach ($env as $line) {
            $hasReference = preg_match($pattern, $line, $matches);

            if (!$hasReference) {
                continue;
            }

            $ref = $matches[3];

            $refValue = $this->env[$ref] ?? null;
            $lineValue = $matches[2];

            if ('${'.$ref.'}' === $lineValue) {
                $lineValueFormatted = $refValue;
            } else {
                $refValue = $this->specialValue()->reverse($refValue);
                $lineValueFormatted = str_replace('${'.$ref.'}', $refValue, $lineValue);
            }

            $this->env[$matches[1]] = $lineValueFormatted;
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
