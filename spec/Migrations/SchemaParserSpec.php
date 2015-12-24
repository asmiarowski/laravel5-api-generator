<?php

namespace spec\Smiarowski\Generators\Migrations;

use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

class SchemaParserSpec extends ObjectBehavior
{
    function it_is_initializable()
    {
        $this->shouldHaveType('Smiarowski\Generators\Migrations\SchemaParser');
    }

    function it_parses_one_field_schema()
    {
        $this->parse('name:string');
        $this->parse('name: string;')
            ->shouldReturn([
            ['name' => 'name', 'type' => 'string', 'arguments' => [], 'options' => []],
            ['name' => 'name', 'type' => 'string', 'arguments' => [], 'options' => []]
        ]);
    }

    function it_parses_multiple_fields_schema()
    {
        $this->parse('name:string; email:email;status:integer')->shouldReturn([
            ['name' => 'name', 'type' => 'string', 'arguments' => [], 'options' => []],
            ['name' => 'email', 'type' => 'email', 'arguments' => [], 'options' => []],
            ['name' => 'status', 'type' => 'integer', 'arguments' => [], 'options' => []],
        ]);
    }

    function it_parses_options_in_schema()
    {
        $this->parse('name:string:nullable:default(15)')->shouldReturn([
            [
                'name' => 'name', 'type' => 'string', 'arguments' => [],
                'options' => ['nullable' => true, 'default' => '15']
            ]
        ]);
    }

    function it_parses_method_arguments_in_schema()
    {
        $this->parse('money:decimal(8,2)')->shouldReturn([
            ['name' => 'money', 'type' => 'decimal', 'arguments' => ['8', '2'], 'options' => []]
        ]);
    }

    function it_parses_field_contstraint_in_schema()
    {
        $this->parse('category_id:integer:foreign')->shouldReturn([
            ['name' => 'category_id', 'type' => 'integer', 'arguments' => [], 'options' => []],
            [
                'name' => 'category_id', 'type' => 'foreign', 'arguments' => [],
                'options' => ['references' => "'id'", 'on' => "'categories'"]
            ]
        ]);
    }

    function it_should_not_allow_wrong_types_in_schema()
    {
        $this->shouldThrow('Smiarowski\Generators\Exceptions\UnsupportedColumnTypeException')->duringParse('wrong_name:wrong_type');
    }
}
