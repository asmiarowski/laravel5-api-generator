<?php

namespace Smiarowski\Generators\Exceptions;

/**
 * Class UnsupportedColumnTypeException
 * @package Smiarowski\Generators\Exceptions
 * Exception thrown when specified type of column does not match anything usable
 */
class UnsupportedColumnTypeException extends \Exception
{
    /**
     * UnsupportedColumnTypeException constructor.
     * @param string $message Type of column that is not supported
     * @param int $code
     * @param \Exception $previous
     */
    public function __construct($message, $code = 0, \Exception $previous = null)
    {
        $message = sprintf('Schema column type of %s is not supported', $message);
        parent::__construct($message, $code, $previous);
    }
}
