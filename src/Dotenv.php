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

    public function __construct($path = '')
    {
        $path = $path ?: realpath(__DIR__.'/../../../../.env');
        $this->setPath($path);

        try {
            $env = \file_exists($this->path) ? \parse_ini_file($this->path, false, INI_SCANNER_RAW) : [];
            $this->env = array_merge($_ENV, getenv(), $env);
        } catch (\Throwable $th) {
            throw new \Exception('An error happened when parsing the .env file: '.$th->getMessage());
        }

        $this->convertSpecials();
        $this->replaceReferences();
    }

    public function convertSpecials()
    {
        foreach ($this->env as $variable => $value) {
            $special = strtolower($value);

            if ($special === 'true' || $special === 'false') {
                $this->env[$variable] = $special === 'true';
            } elseif (is_numeric($value) && strval(intval($value)) === $value) {
                $this->env[$variable] = intval($value);
            }
        }
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
     * @param bool  $overwrite If true, overwrites the variable if it was already in the file
     *
     * @return $this
     */
    public function persist(string $name, $value, bool $overwrite = true, bool $quoteString = true)
    {
        $pattern = '/'.$name.'[ ]*=.*/';
        $content = \file_get_contents($this->path);
        $envVariableExistsInFile = preg_match($pattern, $content);
        $envVariableExistsInMemory = $this->envVariableExistsInMemory($name);

        if (in_array($value, [true, false], true)) {
            $valueToWrite = $value ? 'true' : 'false';
        } else {
            $valueToWrite = is_string($value) && $quoteString ? '"'.$value.'"' : $value;
        }

        $line = $name.'='.$valueToWrite;

        if ($envVariableExistsInFile && $overwrite) {
            $content = preg_replace($pattern, $line, $content);
        } elseif (
            ($envVariableExistsInMemory && $overwrite) ||
            !$envVariableExistsInMemory ||
            !$envVariableExistsInFile
        ) {
            $content = trim($content)."\n\n".$line;
        } elseif (($envVariableExistsInMemory || $envVariableExistsInFile) && !$overwrite) {
            return $this;
        }

        file_put_contents($this->path, $content);

        switch ($value) {
            case 'true':
                $value = true;
                break;

            case 'false':
                $value = false;
                break;
        }

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
        $pattern = '/^([^#;][a-zA-Z0-9_]+)[ ]*=[ ]*(.*\$\{([a-zA-Z0-9_]+)\}.*)/';

        foreach ($env as $line) {
            if (preg_match($pattern, $line, $matches)) {
                $ref = $matches[3];

                if (!$this->envVariableExistsInMemory($ref)) {
                    continue;
                }

                $refValue = $this->env[$ref];
                $lineValue = $matches[2];

                $lineValueFormatted = preg_replace('/\$\{[a-zA-Z0-9_]+\}/', $refValue, $lineValue);

                $lineValueFormatted = $this->properValueOfRef($refValue, $lineValueFormatted);

                $this->env[$matches[1]] = $lineValueFormatted;
            }
        }

        return $this;
    }

    /**
     * Assign the proper type of the reference to the replaced value.
     *
     * @param mixed $refValue
     * @param mixed $lineValue
     */
    protected function properValueOfRef($refValue, $lineValue): string
    {
        if ($this->valueSameAsReference($refValue, $lineValue)) {
            settype($lineValue, gettype($refValue));
        }

        return $lineValue;
    }

    /**
     * Check the value is the same as it reference (It is not inside a sentence for exemple).
     *
     * @param mixed $refValue
     * @param mixed $lineValue
     */
    protected function valueSameAsReference($refValue, $lineValue): bool
    {
        $refValueString = '';
        $refValueType = gettype($refValue);

        if ($this->isStringifiable($refValue)) {
            $refValueString = strval($lineValue);
        }

        return $refValueString === $lineValue;
    }

    /**
     * Check if var can be converted to string.
     *
     * @param mixed $var
     *
     * @see https://stackoverflow.com/a/5496674
     */
    protected function isStringifiable($var): bool
    {
        return
        !is_array($var) &&
            ((!is_object($var) && settype($var, 'string') !== false) ||
            (is_object($var) && method_exists($var, '__toString')));
    }

    /**
     * Determines if an environment variables exists.
     */
    protected function envVariableExistsInMemory(string $name): bool
    {
        return isset($this->env[$name]);
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
