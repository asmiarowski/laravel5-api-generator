<?php

namespace Smiarowski\Generators\Migrations;

class SyntaxBuilder
{
    /**
     * A template to be inserted.
     *
     * @var string
     */
    private $template;

    /**
     * Create the PHP syntax for the given schema.
     *
     * @param  array $schema
     * @param  string $stub
     * @return string
     */
    public function create($schema, $stub)
    {
        $fields = $this->constructSchema($schema);

        return $this->insert($fields)->into($stub);
    }

    /**
     * Store the given template, to be inserted somewhere.
     *
     * @param  string $template
     * @return $this
     */
    private function insert($template)
    {
        $this->template = $template;

        return $this;
    }

    /**
     * Get the stored template, and insert into the given wrapper.
     *
     * @param  string $wrapper
     * @param  string $placeholder
     * @return mixed
     */
    private function into($wrapper, $placeholder = 'schema_up')
    {
        return str_replace('{{' . $placeholder . '}}', $this->template, $wrapper);
    }

    /**
     * Construct the schema fields.
     *
     * @param  array $schema
     * @return string
     */
    private function constructSchema($schema)
    {
        if (!$schema) return '';

        $fields = array_map(function ($field) {
            return $this->addColumn($field);
        }, $schema);

        return implode("\n" . str_repeat(' ', 12), $fields);
    }


    /**
     * Construct the syntax to add a column.
     *
     * @param  string $field
     * @return string
     */
    private function addColumn($field)
    {
        $field['type'] = $this->swapTypes($field['type']);
        $syntax = sprintf("\$table->%s('%s')", $field['type'], $field['name']);

        // If there are arguments for the schema type, like decimal('amount', 5, 2)
        // then we have to remember to work those in.
        if ($field['arguments']) {
            $syntax = substr($syntax, 0, -1) . ', ';

            $syntax .= implode(', ', $field['arguments']) . ')';
        }

        foreach ($field['options'] as $method => $value) {
            $syntax .= sprintf("->%s(%s)", $method, $value === true ? '' : $value);
        }

        return $syntax . ';';
    }

    /**
     * Swap some custom types like email, to available columns created by Laravel
     * @param  string $type
     * @return string
     */
    private function swapTypes($type)
    {
        switch ($type) {
            case 'email':
                return 'string';
            case 'url':
                return 'text';
            default:
                return $type;
        }
    }
}
