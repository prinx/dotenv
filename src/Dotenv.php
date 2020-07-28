<?php
/**
 * (c) Nuna Akpaglo <princedorcis@gmail.com>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Prinx\Dotenv;

/**
 * Main class representing the environment variables
 */
class Dotenv
{
    protected $env = [];
    protected $path = '';
    protected $section_separator = '.';

    public function __construct($path = '')
    {
        $path = $path ?: realpath(__DIR__ . '/../../../../.env');
        $this->setPath($path);

        try {
            $this->env = \parse_ini_file($this->path, true, INI_SCANNER_TYPED);
            $this->env = array_merge($_ENV, $this->env);
        } catch (\Throwable $th) {
            throw new \Exception('An error happened when parsing the <strong>.env</strong> file:<br>' . $th->getMessage());
        }

        $this->replaceReferences();
    }

    /**
     * Replace the references in the .env by their respective value
     *
     * @return void
     */
    public function replaceReferences()
    {
        $env = file($this->path, FILE_SKIP_EMPTY_LINES | FILE_IGNORE_NEW_LINES);
        $pattern = '/^([^#;][a-zA-Z0-9_]+)[ ]*=[ ]*(.*\$\{([a-zA-Z0-9_]+)\}.*)/';

        foreach ($env as $line) {
            if (preg_match($pattern, $line, $matches)) {
                $ref = $matches[3];

                if (!$this->envVariableExists($ref)) {
                    return null;
                }

                $ref_value = $this->env[$ref];
                $line_value = $matches[2];

                $line_value_formatted = preg_replace('/\$\{[a-zA-Z0-9_]+\}/', $ref_value, $line_value);

                $line_value_formatted = $this->properValueOfRef($ref_value, $line_value_formatted);

                $this->env[$matches[1]] = $line_value_formatted;
            }
        }
    }

    /**
     * Assign the proper type of the reference to the replaced value
     *
     * @param mixed $ref_value
     * @param mixed $line_value
     * @return void
     */
    public function properValueOfRef($ref_value, $line_value)
    {
        if ($this->valueSameAsReference($ref_value, $line_value)) {
            settype($line_value, gettype($ref_value));
        }

        return $line_value;
    }

    /**
     * Check the value is the same as it reference (It is not inside a sentence for exemple)
     *
     * @param mixed $ref_value
     * @param mixed $line_value
     * @return void
     */
    public function valueSameAsReference($ref_value, $line_value)
    {
        $ref_value_string = '';
        $ref_value_type = gettype($ref_value);

        if ($this->isStringifiable($ref_value)) {
            $ref_value_string = strval($line_value);
        }

        return $ref_value_string === $line_value;
    }

    /**
     * Check if var can be converted to string
     *
     * @param mixed $var
     * @return bool
     */
    // Thanks to https://stackoverflow.com/a/5496674
    public function isStringifiable($var)
    {
        return (
            !is_array($var) &&
            ((!is_object($var) && settype($var, 'string') !== false) ||
                (is_object($var) && method_exists($var, '__toString')))
        );
    }

    /**
     * Returns all the environment variables
     *
     * @return void
     */
    public function all()
    {
        return $this->env;
    }

    /**
     * Get a specific environment variableq
     *
     * @param string $name
     * @param mixed $default
     * @return void
     */
    public function get($name = '', $default = null)
    {
        if (\func_num_args() === 0) {
            return $this->all();
        }

        if (isset($this->env[$name])) {
            return $this->env[$name];
        } elseif ($value = getenv($name)) {
            return $value;
        }

        $name_exploded = explode($this->section_separator, $name);
        $lookup = $this->env;
        $value = null;

        $last_index = count($name_exploded) - 1;
        foreach ($name_exploded as $key => $variable_name) {
            if (!$variable_name) {
                return null;
            }

            if (isset($lookup[$variable_name])) {
                if (!is_array($value) && $key < $last_index) {
                    return null;
                }

                $lookup = $value;
            } else {
                return \func_num_args() < 2 ? getenv($variable_name) : $default;
            }
        }

        return $value;
    }

    /**
     * Add an environment variables to the currently loaded environment variables
     *
     * @param string $name
     * @param mixed $value
     * @return void
     */
    public function add($name, $value)
    {
        $name_exploded = explode($this->section_separator, $name);

        $namespace_count = count($name_exploded);

        if (1 === $namespace_count) {
            return $this->env[$name] = $value;
        }

        $this->env[$name_exploded[0]] = $this->nextArrayValue(
            $value,
            $name_exploded,
            1,
            $namespace_count - 1
        );
    }

    /**
     * Returns the array corresponding to the current_index in name_indexes or returns the value_to_insert
     *
     * @param mixed $value_to_insert
     * @param array $name_indexes
     * @param int $current_index
     * @param int $last_index
     * @return mixed
     */
    public function nextArrayValue(
        $value_to_insert,
        $name_indexes,
        $current_index,
        $last_index
    ) {
        return $current_index === $last_index ?
        $value_to_insert :
        [
            $name_indexes[$current_index] =>
            $this->nextArrayValue(
                $value_to_insert,
                $name_indexes,
                $current_index + 1,
                $last_index
            ),
        ];
    }
    /**
     * Write a new environment variable to the .env file
     *
     * @param string $name
     * @param mixed $value
     * @param bool $overwrite
     * @param bool $overwrite If true, overwrites the variable if it was already in the file
     * @return void
     */
    public function persist(
        $name,
        $value,
        $overwrite = true,
        $quote_string = true
    ) {
        $pattern = '/' . $name . '[ ]*=.*/';
        $content = \file_get_contents($this->path);
        $env_variable_exists = $this->envVariableExists($name) || preg_match($pattern, $content);

        $value = \is_string($value) && $quote_string ? '"' . $value . '"' : $value;
        $line = $name . "=" . $value;

        if ($env_variable_exists && $overwrite) {
            $content = preg_replace($pattern, $line, $content);
            file_put_contents($this->path, $content);
            $this->add($name, $value);
        } elseif (!$env_variable_exists) {
            $line = $content ? "\n\n" . $line : $line;
            file_put_contents($this->path, $line, FILE_APPEND);
            $this->add($name, $value);
        }
    }

    /**
     * Determines if an environment variables exists
     *
     * @param string $name
     * @return bool
     */
    public function envVariableExists($name)
    {
        return isset($this->env[$name]) || !!getenv($name);
    }

    /**
     * Get the line number of a string, in a file
     *
     * Thanks to https://stackoverflow.com/questions/9721952/search-string-and-return-line-php
     * @param string $fileName
     * @param string $str
     * @return int
     */
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

    /**
     * Add a value to the current loaded environment variables if the value is not already there
     *
     * @param string $name
     * @param mixed $value
     * @param string $section
     * @return void
     */
    public function addIfNotExists($name, $value, $section = '')
    {
        if (!isset($this->env[$name])) {
            $this->add($name, $value, $section);
        }
    }

    /**
     * Set the .env file path
     *
     * @param string $path
     * @return void
     */
    public function setPath($path)
    {
        if (!\file_exists($path)) {
            throw new \Exception('Trying to set the env file path but the file ' . $path . ' seems not to exist.');
        }

        $this->path = $path;
    }

    public function getPath()
    {
        return $this->path;
    }
}
