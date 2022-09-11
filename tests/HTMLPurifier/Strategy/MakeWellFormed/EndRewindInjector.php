<?php

class HTMLPurifier_Strategy_MakeWellFormed_EndRewindInjector extends HTMLPurifier_Injector
{
    public $name = 'EndRewindInjector';
    public $needed = array('span');
    private $deleteElement = false;

    public function handleElement(&$token)
    {
        if ($this->deleteElement) {
            $token = false;
            $this->deleteElement = false;
        }
    }
    public function handleText(&$token)
    {
        $token = false;
    }
    public function handleEnd(&$token)
    {
        $i = null;
        if (
            $this->backward($i, $prev) &&
            $prev instanceof HTMLPurifier_Token_Start &&
            $prev->name == 'span'
        ) {
            $token = false;
            $this->deleteElement = true;
            $this->rewindOffset(1);
        }
    }
}

// vim: et sw=4 sts=4
