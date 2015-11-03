<?php

namespace spec\Smiarowski\Generators\Migrations;

use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

class ValidationBuilderSpec extends ObjectBehavior
{
    function let()
    {
        $this->beConstructedWith('table_test');
        $this->shouldHaveType('Smiarowski\Generators\Migrations\ValidationBuilder');
    }

    function it_should_find_proper_validation_rules()
    {
        $schema = [
            ['name' => 'name', 'type' => 'string', 'arguments' => [], 'options' => []],
            ['name' => 'status', 'type' => 'integer', 'arguments' => [], 'options' => []],
            ['name' => 'email', 'type' => 'email', 'arguments' => [], 'options' => ['unique' => true]],
            ['name' => 'url', 'type' => 'url', 'arguments' => [], 'options' => ['nullable' => true]],
            ['name' => 'i_want_bool', 'type' => 'boolean', 'arguments' => [], 'options' => ['nullable' => true]],
            ['name' => 'i_want_date', 'type' => 'date', 'arguments' => [], 'options' => ['nullable' => true]],
            ['name' => 'i_want_json', 'type' => 'json', 'arguments' => [], 'options' => ['nullable' => true]],
            ['name' => 'i_want_dateTime', 'type' => 'dateTime', 'arguments' => [], 'options' => ['nullable' => true]],
            ['name' => 'i_want_float', 'type' => 'float', 'arguments' => [], 'options' => ['nullable' => true]],
            ['name' => 'i_want_double', 'type' => 'double', 'arguments' => [], 'options' => ['nullable' => true]],
            ['name' => 'i_want_decimal', 'type' => 'decimal', 'arguments' => [], 'options' => ['nullable' => true]],
            ['name' => 'i_want_tinyInteger', 'type' => 'tinyInteger', 'arguments' => [], 'options' => ['nullable' => true]],
            ['name' => 'i_want_smallInteger', 'type' => 'smallInteger', 'arguments' => [], 'options' => ['nullable' => true]],
            ['name' => 'i_want_mediumInteger', 'type' => 'mediumInteger', 'arguments' => [], 'options' => ['nullable' => true]],
            ['name' => 'i_want_bigInteger', 'type' => 'bigInteger', 'arguments' => [], 'options' => ['nullable' => true]],
            ['name' => 'i_want_char', 'type' => 'char', 'arguments' => ['10'], 'options' => ['nullable' => true]],
            ['name' => 'i_want_char_default', 'type' => 'char', 'arguments' => [], 'options' => ['nullable' => true]],
            ['name' => 'i_want_text', 'type' => 'text', 'arguments' => [], 'options' => ['nullable' => true]],
            ['name' => 'i_want_mediumText', 'type' => 'mediumText', 'arguments' => [], 'options' => ['nullable' => true]],
            ['name' => 'i_want_longText', 'type' => 'longText', 'arguments' => [], 'options' => ['nullable' => true]],
            ['name' => 'more_than_zero', 'type' => 'integer', 'arguments' => [], 'options' => ['unsigned' => true]],
            ['name' => 'i_want_jsonb', 'type' => 'jsonb', 'arguments' => [], 'options' => ['nullable' => true]],
            ['name' => 'last_action', 'type' => 'timestamp', 'arguments' => [], 'options' => ['nullable' => true]],
        ];
        $this->build($schema)->shouldReturn([
            'name' => ['required', 'string'],
            'status' => ['required', 'integer'],
            'email' => ['required', 'email', 'unique:table_test'],
            'url' => ['url'],
            'i_want_bool' => ['boolean'],
            'i_want_date' => ['date'],
            'i_want_json' => ['json'],
            'i_want_dateTime' => ['date'],
            'i_want_float' => ['numeric'],
            'i_want_double' => ['numeric'],
            'i_want_decimal' => ['numeric'],
            'i_want_tinyInteger' => ['integer'],
            'i_want_smallInteger' => ['integer'],
            'i_want_mediumInteger' => ['integer'],
            'i_want_bigInteger' => ['integer'],
            'i_want_char' => ['string', 'size:10'],
            'i_want_char_default' => ['string', 'size:255'],
            'i_want_text' => ['string'],
            'i_want_mediumText' => ['string'],
            'i_want_longText' => ['string'],
            'more_than_zero' => ['required', 'integer', 'min:0'],
            'i_want_jsonb' => ['json'],
            'last_action' => ['date_format:Y-m-d H:i:s'],
        ]);
    }
}
