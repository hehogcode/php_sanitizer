<?php


namespace SanitizerTest;

require "..\Sanitizer\Sanitizer.php";

use PHPUnit\Framework\TestCase;
use Sanitizer\Sanitizer;

class SanitizerTest extends TestCase
{
    /**
     * @dataProvider data_provider_no_errors
     * @dataProvider data_provider_errors
     */
    public function test($json_data, $json_types, $expected, $expected_errors)
    {
        $sanitizer = new Sanitizer();

        $sanitizer->set_data($json_data, $json_types);
        $result = $sanitizer->get_object();
        $errors = $sanitizer->get_errors();

        $expected = json_decode($expected);
        $expected_errors = empty($expected_errors) ? array() : json_decode($expected_errors);
        $this->assertEquals($expected, $result);
        $this->assertEquals($expected_errors, $errors);
    }


    public function data_provider_no_errors()
    {
        return array(
            "phone numbers 1" => ['{"foo": "123", "bar": "asd", "baz": "8 (950) 288-56-23"}', '{"foo": "integer", "bar": "string", "baz": "phone"}', '{"foo": 123, "bar": "asd", "baz": "79502885623"}', ''],
            "phone numbers 2" => ['{"foo": "123", "bar": "asd", "baz": "89502885623"}', '{"foo": "integer", "bar": "string", "baz": "phone"}', '{"foo": 123, "bar": "asd", "baz": "79502885623"}', ''],
            "phone numbers 3" => ['{"foo": "123", "bar": "asd", "baz": "+79502885623"}', '{"foo": "integer", "bar": "string", "baz": "phone"}', '{"foo": 123, "bar": "asd", "baz": "79502885623"}', ''],
            "integer testing" => ['{"a": "123", "b": "322", "c": "-2323232"}', '{"a": "integer", "b": "integer", "c": "integer"}', '{"a": 123, "b": 322, "c": -2323232}', ''],
            "float testing" => ['{"a": "123.3232", "b": "-12.322", "c": "123.23e5", "d": "322.12e-5"}', '{"a": "float", "b": "float", "c": "float", "d": "float"}', '{"a": 123.3232, "b": -12.322, "c": 12323000, "d": 0.0032212}', ''],
            "testing arrays 1" => ['{"phones": ["8 (950) 288-56-23", "8 (950) 288-56-24", "8 (950) 288-56-25"]}', '{"phones": "phone"}', '{"phones": ["79502885623", "79502885624", "79502885625"]}', ''],
            "testing arrays 2" => ['{"numbers": ["10", "20", "30", "40", "50", "60", "70"]}', '{"numbers": "integer"}', '{"numbers": [10, 20, 30, 40, 50, 60, 70]}', ''],
            "testing structures" => ['{"a": {"b": "c", "d": {"e": "f", "g": "8 (800) 555-35-35"}}}', '{"a": {"b": "string", "d": {"e": "string", "g": "phone"}}}', '{"a": {"b": "c", "d": {"e": "f", "g": "78005553535"}}}', '']
        );
    }

    public function data_provider_errors()
    {
        return array(
            "integer error" => ['{"foo": "123asd", "bar": "12 3", "baz": "8 (950) 288-56-23"}', '{"foo": "integer", "bar": "integer", "baz": "phone"}', '{"baz": "79502885623"}', '["Value <123asd> does not match the format type <integer>", "Value <12 3> does not match the format type <integer>"]'],
            "float error" => ['{"foo": "123asd.22", "bar": "23.22ss", "baz": "23...2323"}', '{"foo": "float", "bar": "float", "baz": "float"}', '{}', '["Value <123asd.22> does not match the format type <float>", "Value <23.22ss> does not match the format type <float>", "Value <23...2323> does not match the format type <float>"]'],
            "phone error" => ['{"a": "99502885623", "b": "+7  9502   885623", "c": "+7(950)288-5623", "d": "8950285623"}', '{"a": "phone", "b": "phone", "c": "phone", "d": "phone"}', '{}', '["Value <99502885623> does not match the format type <phone>", "Value <+7  9502   885623> does not match the format type <phone>", "Value <+7(950)288-5623> does not match the format type <phone>", "Value <8950285623> does not match the format type <phone>"]'],
            "array error 1" => ['{"a": ["12a", "23s", "2322s", "12", "232"]}', '{"a": "integer"}', '{"a": [12, 232]}', '["Value <12a> does not match the format type <integer>", "Value <23s> does not match the format type <integer>", "Value <2322s> does not match the format type <integer>"]'],
            "array error 2" => ['{"a": ["12a", "23s", "2322s", "12q", "232w"]}', '{"a": "integer"}', '{"a": []}', '["Value <12a> does not match the format type <integer>", "Value <23s> does not match the format type <integer>", "Value <2322s> does not match the format type <integer>", "Value <12q> does not match the format type <integer>", "Value <232w> does not match the format type <integer>"]'],
            "structure error 1" => ['{"a": {"b": "c", "d": {"e": "f", "g": "8 (800) 555-35-35"}}}', '{"a": {"b": "integer", "d": {"e": "integer", "g": "phone"}}}', '{"a": {"d": {"g": "78005553535"}}}', '["Value <c> does not match the format type <integer>", "Value <f> does not match the format type <integer>"]'],
            "structure error 2" => ['{"a": {"b": "c", "d": {"e": "f", "g": "8 (800) 555-35-35"}}}', '{"a": {"b": "integer", "d": {"e": "integer", "g": "integer"}}}', '{"a": {"d": {}}}', '["Value <c> does not match the format type <integer>", "Value <f> does not match the format type <integer>", "Value <8 (800) 555-35-35> does not match the format type <integer>"]']
        );
    }
}