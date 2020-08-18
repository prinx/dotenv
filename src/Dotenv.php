<?php

/*
 * (c) Nuna Akpaglo <princedorcis@gmail.com>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Prinx\Dotenv;

/**
 * Main class representing the environment variables.
 */
class Dotenv
{
    protected $env = [];
    protected $path = '';
    protected $sectionSeparator = '.';

    public function __construct($path = '')
    {
        $path = $path ?: realpath(__DIR__.'/../../../../.env');
        $this->setPath($path);

        try {
            $this->env = \parse_ini_file($this->path, true, INI_SCANNER_TYPED);
            $this->env = array_merge($_ENV, $this->env);
        } catch (\Throwable $th) {
            throw new \Exception('An error happened when parsing the <strong>.env</strong> file:<br>'.$th->getMessage());
        }

        $this->replaceReferences();
    }

    /**
     * Returns all the environment variables.
     *
     * @return array
     */
    public function all()
    {
        return $this->env;
    }

    /**
     * Get a specific environment variable.
     *
     * @param  string  $name
     * @param  mixed   $default
     * @return mixed
     */
    public function get($name = '', $default = null)
    {
        if (\func_num_args() === 0) {
            return $this->all();
        }

        $defaultWasPassed = \func_num_args() === 2;

        if (isset($this->env[$name])) {
            if ('' === $this->env[$name]) {
                return $defaultWasPassed ? $default : '';
            }

            return $this->env[$name];
        } elseif ($value = getenv($name)) {
            return $value;
        }

        $nameExploded = explode($this->sectionSeparator, $name);
        $lookup = $this->env;
        $value = null;

        $lastIndex = count($nameExploded) - 1;
        foreach ($nameExploded as $key => $variableName) {
            if (!$variableName) {
                return null;
            }

            if (isset($lookup[$variableName])) {
                if (!is_array($value) && $key < $lastIndex) {
                    return null;
                }

                $lookup = $value;
            } else {
                return $defaultWasPassed ? $default : getenv($variableName);
            }
        }

        return $value;
    }

    /**
     * Add an environment variables to the currently loaded environment variables.
     *
     * @param  string $name
     * @param  mixed  $value
     * @return void
     */
    public function add($name, $value)
    {
        $nameExploded = explode($this->sectionSeparator, $name);

        $namespaceCount = count($nameExploded);

        if (1 === $namespaceCount) {
            return $this->env[$name] = $value;
        }

        $this->env[$nameExploded[0]] = $this->nextArrayValue(
            $value,
            $nameExploded,
            1,
            $namespaceCount - 1
        );
    }

    /**
     * Write a new environment variable to the .env file.
     *
     * @param  string $name
     * @param  mixed  $value
     * @param  bool   $overwrite      If true, overwrites the variable if it was already in the file
     * @param  bool   $quoteString
     * @return void
     */
    public function persist($name, $value, $overwrite = true, $quoteString = true)
    {
        $pattern = '/'.$name.'[ ]*=.*/';
        $content = \file_get_contents($this->path);
        $envVariableExistsInFile = preg_match($pattern, $content);
        $envVariableExistsInMemory = $this->envVariableExistsInMemory($name);

        $value = \is_string($value) && $quoteString ? '"'.$value.'"' : $value;
        $line = $name.'='.$value;

        if ($envVariableExistsInFile && $overwrite) {
            $content = preg_replace($pattern, $line, $content);
        } elseif (
            ($envVariableExistsInMemory && $overwrite) ||
            !$envVariableExistsInMemory ||
            !$envVariableExistsInFile
        ) {
            $content = trim($content)."\n\n".$line;
        } elseif (($envVariableExistsInMemory || $envVariableExistsInFile) && !$overwrite) {
            return;
        }

        file_put_contents($this->path, $content);
        $this->add($name, $value);
    }

    public static function load($path)
    {
        return new self($path);
    }

    /**
     * Replace the references in the .env by their respective value.
     *
     * @return void
     */
    protected function replaceReferences()
    {
        $env = file($this->path, FILE_SKIP_EMPTY_LINES | FILE_IGNORE_NEW_LINES);
        $pattern = '/^([^#;][a-zA-Z0-9_]+)[ ]*=[ ]*(.*\$\{([a-zA-Z0-9_]+)\}.*)/';

        foreach ($env as $line) {
            if (preg_match($pattern, $line, $matches)) {
                $ref = $matches[3];

                if (!$this->envVariableExistsInMemory($ref)) {
                    return null;
                }

                $refValue = $this->env[$ref];
                $lineValue = $matches[2];

                $lineValueFormatted = preg_replace('/\$\{[a-zA-Z0-9_]+\}/', $refValue, $lineValue);

                $lineValueFormatted = $this->properValueOfRef($refValue, $lineValueFormatted);

                $this->env[$matches[1]] = $lineValueFormatted;
            }
        }
    }

    /**
     * Assign the proper type of the reference to the replaced value.
     *
     * @param  mixed  $refValue
     * @param  mixed  $lineValue
     * @return void
     */
    protected function properValueOfRef($refValue, $lineValue)
    {
        if ($this->valueSameAsReference($refValue, $lineValue)) {
            settype($lineValue, gettype($refValue));
        }

        return $lineValue;
    }

    /**
     * Check the value is the same as it reference (It is not inside a sentence for exemple).
     *
     * @param  mixed  $refValue
     * @param  mixed  $lineValue
     * @return void
     */
    protected function valueSameAsReference($refValue, $lineValue)
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
     * @param  mixed  $var
     * @return bool
     */
    // Thanks to https://stackoverflow.com/a/5496674
    protected function isStringifiable($var)
    {
        return
        !is_array($var) &&
            ((!is_object($var) && settype($var, 'string') !== false) ||
            (is_object($var) && method_exists($var, '__toString')));
    }

    /**
     * Returns the array corresponding to the currentIndex in nameIndexes or returns the valueToInsert.
     *
     * @param  mixed   $valueToInsert
     * @param  array   $nameIndexes
     * @param  int     $currentIndex
     * @param  int     $lastIndex
     * @return mixed
     */
    protected function nextArrayValue(
        $valueToInsert,
        $nameIndexes,
        $currentIndex,
        $lastIndex
    ) {
        return $currentIndex === $lastIndex ?
        $valueToInsert :
        [
            $nameIndexes[$currentIndex] => $this->nextArrayValue(
                $valueToInsert,
                $nameIndexes,
                $currentIndex + 1,
                $lastIndex
            ),
        ];
    }

    /**
     * Determines if an environment variables exists.
     *
     * @param  string $name
     * @return bool
     */
    protected function envVariableExistsInMemory($name)
    {
        return isset($this->env[$name]) || (bool) getenv($name);
    }

    /**
     * Get the line number of a string, in a file.
     *
     * Thanks to https://stackoverflow.com/questions/9721952/search-string-and-return-line-php
     * @param  string $fileName
     * @param  string $str
     * @return int
     */
    protected function getLineWithString($fileName, $str)
    {
        $lines = file($fileName);
        foreach ($lines as $line) {
            if (strpos($line, $str) !== false) {
                return $line;
            }
        }

        return -1;
    }

    /**
     * Add a value to the current loaded environment variables if the value is not already there.
     *
     * @param  string $name
     * @param  mixed  $value
     * @param  string $section
     * @return void
     */
    protected function addIfNotExists($name, $value, $section = '')
    {
        if (!isset($this->env[$name])) {
            $this->add($name, $value, $section);
        }
    }

    public function setPath($path)
    {
        if (!\file_exists($path)) {
            throw new \Exception('Trying to set the env file path but the file '.$path.' seems not to exist.');
        }

        $this->path = $path;
    }

    public function getPath()
    {
        return $this->path;
    }
}
