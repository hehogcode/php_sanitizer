<?php

namespace Sanitizer\Filters;

require 'Filters.php';

class FilterRegister
{
    private $filter_types = array();

    public function register($name, $filter_instance)
    {
        $this->filter_types[$name] = $filter_instance;
    }

    public function get($name)
    {
        return $this->filter_types[$name];
    }

    public function get_supported_filters()
    {
        return join(", ", array_keys($this->filter_types));
    }
}


function create_filter_register()
{
    $filters = new FilterRegister();
    $filters->register('integer', new IntegerFilter());
    $filters->register('float', new FloatFilter());
    $filters->register('phone', new PhoneFilter());
    $filters->register('string', new StringFilter());
    return $filters;
}




