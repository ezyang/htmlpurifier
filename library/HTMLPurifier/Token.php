<?php

/**
 * Defines a set of immutable value object tokens for HTML representation.
 * 
 * @file
 */

/**
 * Abstract base token class that all others inherit from.
 */
class HTMLPurifier_Token {
    public $type; /**< Type of node to bypass <tt>is_a()</tt>. */
    public $line; /**< Line number node was on in source document. Null if unknown. */
    
    /**
     * Lookup array of processing that this token is exempt from.
     * Currently, valid values are "ValidateAttributes" and
     * "MakeWellFormed_TagClosedError"
     */
    public $armor = array();
    
    /**
     * Copies the tag into a new one (clone substitute).
     * @return Copied token
     */
    public function copy() {
        return unserialize(serialize($this));
    }
}

/**
 * Abstract class of a tag token (start, end or empty), and its behavior.
 */
class HTMLPurifier_Token_Tag extends HTMLPurifier_Token // abstract
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
     * Non-overloaded constructor, which lower-cases passed tag name.
     * 
     * @param $name String name.
     * @param $attr Associative array of attributes.
     */
    public function __construct($name, $attr = array(), $line = null) {
        $this->name = ctype_lower($name) ? $name : strtolower($name);
        foreach ($attr as $key => $value) {
            // normalization only necessary when key is not lowercase
            if (!ctype_lower($key)) {
                $new_key = strtolower($key);
                if (!isset($attr[$new_key])) {
                    $attr[$new_key] = $attr[$key];
                }
                if ($new_key !== $key) {
                    unset($attr[$key]);
                }
            }
        }
        $this->attr = $attr;
        $this->line = $line;
    }
}

/**
 * Concrete start token class.
 */
class HTMLPurifier_Token_Start extends HTMLPurifier_Token_Tag
{
    public $type = 'start';
}

/**
 * Concrete empty token class.
 */
class HTMLPurifier_Token_Empty extends HTMLPurifier_Token_Tag
{
    public $type = 'empty';
}

/**
 * Concrete end token class.
 * 
 * @warning This class accepts attributes even though end tags cannot. This
 * is for optimization reasons, as under normal circumstances, the Lexers
 * do not pass attributes.
 */
class HTMLPurifier_Token_End extends HTMLPurifier_Token_Tag
{
    public $type = 'end';
}

/**
 * Concrete text token class.
 * 
 * Text tokens comprise of regular parsed character data (PCDATA) and raw
 * character data (from the CDATA sections). Internally, their
 * data is parsed with all entities expanded. Surprisingly, the text token
 * does have a "tag name" called #PCDATA, which is how the DTD represents it
 * in permissible child nodes.
 */
class HTMLPurifier_Token_Text extends HTMLPurifier_Token
{
    
    public $name = '#PCDATA'; /**< PCDATA tag name compatible with DTD. */
    public $type = 'text';
    public $data; /**< Parsed character data of text. */
    public $is_whitespace; /**< Bool indicating if node is whitespace. */
    
    /**
     * Constructor, accepts data and determines if it is whitespace.
     * 
     * @param $data String parsed character data.
     */
    public function __construct($data, $line = null) {
        $this->data = $data;
        $this->is_whitespace = ctype_space($data);
        $this->line = $line;
    }
    
}

/**
 * Concrete comment token class. Generally will be ignored.
 */
class HTMLPurifier_Token_Comment extends HTMLPurifier_Token
{
    public $data; /**< Character data within comment. */
    public $type = 'comment';
    /**
     * Transparent constructor.
     * 
     * @param $data String comment data.
     */
    public function __construct($data, $line = null) {
        $this->data = $data;
        $this->line = $line;
    }
}

