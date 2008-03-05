<?php

/**
 * Parses string representations into their corresponding native PHP
 * variable type.
 */
abstract class HTMLPurifier_VarParser
{
    
    /**
     * Lookup table of allowed types.
     */
    static public $types = array(
        'string'    => true,
        'istring'   => true,
        'text'      => true,
        'itext'     => true,
        'int'       => true,
        'float'     => true,
        'bool'      => true,
        'lookup'    => true,
        'list'      => true,
        'hash'      => true,
        'mixed'     => true
    );
    
    /**
     * Validate a variable according to type. Throws
     * HTMLPurifier_VarParserException if invalid.
     * It may return NULL as a valid type if $allow_null is true.
     *
     * @param $var Variable to validate
     * @param $type Type of variable, see HTMLPurifier_VarParser->types
     * @param $allow_null Whether or not to permit null as a value
     * @return Validated and type-coerced variable
     */
    abstract public function parse($var, $type, $allow_null = false);
    
}
