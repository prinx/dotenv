<?php

namespace Prinx\Dotenv;

class SpecialValue
{
    protected $valueTypes = ['Bool', 'Integer'];

    public function confirm($value)
    {
        foreach ($this->valueTypes as $type) {
            if ($this->{'is'.$type}($value)) {
                return true;
            }
        }

        return false;
    }

    public function convert($value)
    {
        foreach ($this->valueTypes as $type) {
            if ($this->{'is'.$type}($value)) {
                return $this->{'convertTo'.$type}($value);
            }
        }

        return $value;
    }

    public function reverse($value)
    {
        foreach ($this->valueTypes as $type) {
            if ($this->{'is'.$type}($value)) {
                return $this->{'reverse'.$type}($value);
            }
        }

        return $value;
    }

    public function isBool($value)
    {
        return in_array(strtolower($value), [true, false, 'true', 'false'], true);
    }

    public function convertToBool($value)
    {
        return strtolower($value) === 'true';
    }

    public function reverseBool(bool $value)
    {
        return $value === false ? 'false' : 'true';
    }

    public function isInteger($value)
    {
        return is_numeric($value) && strval(intval($value)) === $value;
    }

    public function convertToInteger($value)
    {
        return intval($value);
    }

    public function reverseInteger($value)
    {
        return strval($value);
    }
}
