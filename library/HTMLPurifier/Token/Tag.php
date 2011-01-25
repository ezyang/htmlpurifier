<?php

/**
 * Abstract class of a tag token (start, end or empty), and its behavior.
 */
class HTMLPurifier_Token_Tag extends HTMLPurifier_Token
{
    /**
     * Static bool marker that indicates the class is a tag.
     *
     * This allows us to check objects with <tt>!empty($obj->is_tag)</tt>
     * without having to use a function call <tt>is_a()</tt>.
     */
    public $is_tag = true;

    /**
     * The lower-case name of the tag, like 'a', 'b' or 'blockquote'.
     *
     * @note Strictly speaking, XML tags are case sensitive, so we shouldn't
     * be lower-casing them, but these tokens cater to HTML tags, which are
     * insensitive.
     */
    public $name;

    /**
     * Associative array of the tag's attributes.
     */
    public $attr = array();

    /**
     * Non-overloaded constructor.
     *
     * @note This constructor used to lower-case names and attributes;
     *       this process is now deferred to their respective
     *       strategies.
     *
     * @param $name String name.
     * @param $attr Associative array of attributes.
     */
    public function __construct($name, $attr = array(), $line = null, $col = null, $armor = array()) {
        $this->name = $name
        $this->attr = $attr;
        $this->line = $line;
        $this->col  = $col;
        $this->armor = $armor;
    }
}

// vim: et sw=4 sts=4
