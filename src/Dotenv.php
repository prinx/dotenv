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
    protected $section_separator = '.';

    public function __construct($path = '')
    {
        $path = $path ?: realpath(__DIR__ . '/../../../../.env');
        $this->setPath($path);

        try {
            $this->env = \parse_ini_file($this->path, true, INI_SCANNER_TYPED);
        } catch (\Throwable $th) {
            throw new \Exception('An error happened when parsing the .env file: ' . $th->getMessage());
        }

        $this->replaceReferences();
    }

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

    public function properValueOfRef($ref_value, $line_value)
    {
        if ($this->valueSameAsReference($ref_value, $line_value)) {
            settype($line_value, gettype($ref_value));
        }

        return $line_value;
    }

    public function valueSameAsReference($ref_value, $line_value)
    {
        $ref_value_string = '';
        $ref_value_type = gettype($ref_value);

        if ($this->isStringifiable($ref_value)) {
            $ref_value_string = strval($line_value);
        }

        return $ref_value_string === $line_value;
    }

    // Thanks to https://stackoverflow.com/a/5496674
    public function isStringifiable($var)
    {
        return (
            !is_array($var) &&
            ((!is_object($var) && settype($var, 'string') !== false) ||
                (is_object($var) && method_exists($var, '__toString')))
        );
    }

    public function all()
    {
        return $this->env;
    }

    public function get($name = null, $default = null)
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
        $value = false;

        $last_index = count($name_exploded) - 1;
        foreach ($name_exploded as $key => $variable_name) {
            if (!$variable_name) {
                return false;
            }

            if (isset($lookup[$variable_name])) {
                if (!is_array($value) && $key < $last_index) {
                    return false;
                }

                $lookup = $value;
            } else {
                return \func_num_args() < 2 ? getenv($variable_name) : $default;
            }
        }

        return $value;
    }

    /**
     * Section not yet support
     */
    public function add($name, $value)
    {
        $name_exploded = explode($this->section_separator, $name);

        $namespace_count = count($name_exploded);

        if ($namespace_count === 1) {
            return $this->env[$name] = $value;
        }

        $this->env[$name_exploded[0]] = $this->nextArrayValue(
            $value,
            $name_exploded,
            1,
            $namespace_count - 1
        );
    }

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
        return isset($this->env[$name]);
    }

    // Thanks to https://stackoverflow.com/questions/9721952/search-string-and-return-line-php
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
