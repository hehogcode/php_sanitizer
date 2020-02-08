<?php

namespace Sanitizer;

require_once 'Filters/FilterRegister.php';

use function Sanitizer\Filters\create_filter_register;

class Sanitizer
{
    private $json_data = array();
    private $json_types = array();
    private $errors = array();
    private $filters_register;

    public function __construct()
    {
        $this->filters_register = create_filter_register();
    }

    public function set_data($json_string, $json_types)
    {
        $this->json_data = json_decode($json_string);
        $this->json_types = json_decode($json_types);
        $this->errors = array();
    }

    public function get_errors()
    {
        return $this->errors;
    }

    public function get_object()
    {
        $this->errors = array();
        return $this->sanitize_object($this->json_data, $this->json_types);
    }

    private function sanitize_object($data, $types)
    {
        $object = new \stdClass();

        foreach ($data as $key => $value) {
            $sanitized = NULL;

            if (is_object($value)) {
                $sanitized = $this->sanitize_object($value, $types->{$key});
            } else if (is_array($value)) {
                $sanitized = array();
                foreach ($value as $element) {
                    $sanitized_element = $this->sanitize_value($element, $types->{$key});
                    if (!is_null($sanitized_element)) {
                        array_push($sanitized, $sanitized_element);
                    }
                }
            } else {
                $sanitized = $this->sanitize_value($value, $types->{$key});
            }

            if (!is_null($sanitized)) {
                $object->{$key} = $sanitized;
            }
        }

        return $object;
    }

    private function sanitize_value($value, $type)
    {
        $filter = $this->filters_register->get($type);
        if ($filter->validate($value)) {
            return $filter->clear($value);
        } else {
            array_push($this->errors, "Value <{$value}> does not match the format type <{$type}>");
            return NULL;
        }
    }
}