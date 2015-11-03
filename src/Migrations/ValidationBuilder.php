<?php

namespace Smiarowski\Generators\Migrations;

class ValidationBuilder
{
    /**
     * @var string
     */
    protected $tableName;
    /**
     * @var bool
     */
    protected $softDeletes;
    /**
     * @var array
     */
    protected $validationRules = [];

    public function __construct($tableName, $softDeletes = false)
    {
        $this->tableName = $tableName;
        $this->softDeletes = $softDeletes;
    }

    /**
     * Build validation rules for Request class
     * @param  array $schema
     * @return array
     */
    public function build(array $schema)
    {
        foreach ($schema as $s) {
            $this->validationRules[$s['name']] = ['required'];
            $this->typeToValidator($s['name'], $s['type']);
            $this->optionsToValidator($s['name'], $s['options']);
            $this->argumentsToValidator($s['name'], $s['type'], $s['arguments']);
        }

        return $this->validationRules;
    }

    /**
     * Finds validation rule for field type specified in schema building
     * @param string $name
     * @param string $type
     * @return void
     */
    protected function typeToValidator($name, $type)
    {
        switch ($type) {
            case 'string':
            case 'integer':
            case 'email':
            case 'url':
            case 'boolean':
            case 'date':
            case 'json':
                $this->validationRules[$name][] = $type;
                break;
            case 'dateTime':
                $this->validationRules[$name][] = 'date';
                break;
            case 'float':
            case 'double':
            case 'decimal':
                $this->validationRules[$name][] = 'numeric';
                break;
            case 'tinyInteger':
            case 'smallInteger':
            case 'mediumInteger':
            case 'bigInteger':
                $this->validationRules[$name][] = 'integer';
                break;
            case 'char':
            case 'text':
            case 'mediumText':
            case 'longText':
                $this->validationRules[$name][] = 'string';
                break;
            case 'jsonb':
                $this->validationRules[$name][] = 'json';
                break;
            case 'timestamp':
                $this->validationRules[$name][] = 'date_format:Y-m-d H:i:s';
                break;
        }
    }

    /**
     * Finds validation rules for options part of schema
     * @param string $name
     * @param array $options
     * @return void
     */
    protected function optionsToValidator($name, array $options)
    {
        foreach ($options as $key => $value) {
            if (is_string($value)) $value = str_replace('\'', '', $value);
            if ($key == 'unique' && $value) $this->validationRules[$name][] = sprintf('unique:%s', $this->tableName);
            if ($key == 'on') $this->validationRules[$name][] = sprintf('exists:%s,id', $value);
            if ($key == 'nullable' && $value) $this->validationRules[$name] = array_values(array_diff($this->validationRules[$name], ['required']));
            if ($key == 'unsigned' && $value) $this->validationRules[$name][] = 'min:0';
        }
    }

    /**
     * Finds validation rules for arguments part of schema
     * @param string $name
     * @param string $type
     * @param array $arguments
     */
    protected function argumentsToValidator($name, $type, array $arguments)
    {
        switch ($type) {
            case 'char':
                $size = !empty($arguments[0]) ? $arguments[0] : '255';
                $this->validationRules[$name][] = sprintf('size:%s', $size);
                break;
        }
    }


}
