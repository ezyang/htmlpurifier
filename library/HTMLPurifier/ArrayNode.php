<?php

class HTMLPurifier_ArrayNode
{
    public function __construct(&$value)
    {
        $this->value = &$value;
    }

    /**
     * @var HTMLPurifier_ArrayNode
     */
    public $prev = null;

    /**
     * @var HTMLPurifier_ArrayNode
     */
    public $next = null;

    /**
     * @var mixed
     */
    public $value = null;
}
