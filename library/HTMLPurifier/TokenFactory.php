<?php

require_once 'HTMLPurifier/Token.php';

class HTMLPurifier_TokenFactory
{
    
    // p stands for prototype
    private $p_start, $p_end, $p_empty, $p_text, $p_comment;
    
    public function __construct() {
        $this->p_start  = new HTMLPurifier_Token_Start('', array());
        $this->p_end    = new HTMLPurifier_Token_End('');
        $this->p_empty  = new HTMLPurifier_Token_Empty('', array());
        $this->p_text   = new HTMLPurifier_Token_Text('');
        $this->p_comment= new HTMLPurifier_Token_Comment('');
    }
    
    public function createStart($name, $attributes = array()) {
        $p = clone $this->p_start;
        $p->HTMLPurifier_Token_Tag($name, $attributes);
        return $p;
    }
    
    public function createEnd($name) {
        $p = clone $this->p_end;
        $p->HTMLPurifier_Token_Tag($name);
        return $p;
    }
    
    public function createEmpty($name, $attributes = array()) {
        $p = clone $this->p_empty;
        $p->HTMLPurifier_Token_Tag($name, $attributes);
        return $p;
    }
    
    public function createText($data) {
        $p = clone $this->p_text;
        $p->HTMLPurifier_Token_Text($data);
        return $p;
    }
    
    public function createComment($data) {
        $p = clone $this->p_comment;
        $p->HTMLPurifier_Token_Comment($data);
        return $p;
    }
    
}

?>