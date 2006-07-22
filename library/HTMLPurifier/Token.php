<?php

// all objects here are immutable

class HTMLPurifier_Token {} // abstract

class HTMLPurifier_Token_Tag extends HTMLPurifier_Token // abstract
{
    var $is_tag = true;
    var $name;
    function HTMLPurifier_Token_Tag($name) {
        // watch out, actually XML is case-sensitive, while HTML
        // is case insensitive, which means we can't use this for XML
        $this->name = strtolower($name); // for some reason, the SAX parser
                                         // uses uppercase. Investigate?
    }
}

// a rich tag has attributes
class HTMLPurifier_Token_RichTag extends HTMLPurifier_Token_Tag // abstract
{
    var $attributes = array();
    function HTMLPurifier_Token_RichTag($name, $attributes = array()) {
        $this->HTMLPurifier_Token_Tag($name);
        $this->attributes = $attributes;
    }
}

// start CONCRETE ones

class HTMLPurifier_Token_Start extends HTMLPurifier_Token_RichTag
{
    var $type = 'start';
}

class HTMLPurifier_Token_Empty extends HTMLPurifier_Token_RichTag
{
    var $type = 'empty';
}

class HTMLPurifier_Token_End extends HTMLPurifier_Token_Tag
{
    var $type = 'end';
}

class HTMLPurifier_Token_Text extends HTMLPurifier_Token
{
    var $name = '#PCDATA';
    var $type = 'text';
    var $data;
    var $is_whitespace = false;
    function HTMLPurifier_Token_Text($data) {
        $this->data = $data;
        if (trim($data, " \n\r\t") === '') $this->is_whitespace = true;
    }
    function append($text) {
        return new HTMLPurifier_Token_Text($this->data . $text->data);
    }
}

class HTMLPurifier_Token_Comment extends HTMLPurifier_Token
{
    var $data;
    var $type = 'comment';
    function HTMLPurifier_Token_Comment($data) {
        $this->data = $data;
    }
}

?>