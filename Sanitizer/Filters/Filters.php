<?php

namespace Sanitizer\Filters;


abstract class Filter
{
    protected $validation_regexp = array();
    protected $clean_regexp = array();

    protected function type_cast($value)
    {
        return $value;
    }

    public function validate($value)
    {
        foreach ($this->validation_regexp as $regexp) {
            if (preg_match($regexp, $value)) {
                return true;
            }
        }

        return false;
    }

    public function clear($value)
    {
        foreach ($this->clean_regexp as $regexp => $replacement) {
            $value = preg_replace($regexp, $replacement, $value);
        }

        return $this->type_cast($value);
    }
}


class IntegerFilter extends Filter
{
    public function __construct()
    {
        $this->validation_regexp = array(
            "/^[+-]?[1-9]{1}\d+$/",
            "/^[+-]?0$/"
        );
    }

    function type_cast($value)
    {
        return (int)$value;
    }
}


class FloatFilter extends Filter
{
    public function __construct()
    {
        $this->validation_regexp = array(
            "/^[+-]?\d+\.?\d+$/",
            "/^[+-]?\d+\.?\d+[eE][+-]?\d+$/",
            "/^[+-]?\d+[eE][+-]?\d+$/"
        );
    }

    function type_cast($value)
    {
        return (float)$value;
    }
}


class StringFilter extends Filter
{
    public function __construct()
    {
        $this->validation_regexp = array("/^.*$/");
        $this->clean_regexp = array(
            "/ {2,}/" => " ",
            "/[\n\t]/" => "",
        );
    }
}


class PhoneFilter extends Filter
{
    public function __construct()
    {
        $this->validation_regexp = array(
            "/^(8|\+7)( ?\()\d{3}(\) ?)\d{3}-\d{2}-\d{2}$/",
            "/^(8|\+7)\d{10}$/"
        );
        $this->clean_regexp = array(
            "/ +/" => "",
            "/[()-]/" => "",
            "/^(8|\+7)/" => "7",
        );
    }
}