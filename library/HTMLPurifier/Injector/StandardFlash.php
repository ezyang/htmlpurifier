<?php

/**
 * changes objects to a standard object
 */
class HTMLPurifier_Injector_StandardFlash extends HTMLPurifier_Injector
{
    //kept this stuff in here, not sure what it does
    public $name = 'StandardFlash';
    public $needed = array('object', 'param', 'embed');

    protected $attribs_and_params  = array();

     protected $depth = 0;

     protected $from_a_rewind = 0;

    public function prepare($config, $context) {
        parent::prepare($config, $context);
    }

     public function handleText(&$token) {
          //if in an object, remove the text tokens
          if($this->depth > 0){
               $token = array();
          }
          return;
     }

    public function handleElement(&$token) {

        //this was called from a rewind (it is a start tag adjacent to the end tag)
        if($this->from_a_rewind ==1){
            $this->from_a_rewind = 0;
            //if this is not the original object, delete it
            if($this->depth > 0){
                //echo ' DELETE';
                $token = 2;
            }else{
                //if this is the original object, convert it
                $token = new HTMLPurifier_Token_Span($this->attribs_and_params);
            }
            return;
        }

        //if a first embed
        if($this->depth == 0 && $token->name == 'embed' && $token instanceof HTMLPurifier_Token_Empty){
            $this->attribs_and_params = array();
            $this->attribs_and_params["movie"] = $token->attr['src'];
            $this->attribs_and_params["data"] = $token->attr['src'];
            $this->attribs_and_params["width"] = $token->attr['width'];
            $this->attribs_and_params["height"] = $token->attr['height'];
            $this->attribs_and_params["flashvars"] = $token->attr['flashvars'];

            $token = new HTMLPurifier_Token_Span($this->attribs_and_params);

            return;
        }

        //if a first object
        if($this->depth == 0 && $token->name == 'object' && $token instanceof HTMLPurifier_Token_Start){
            //now in an object
            $this->depth++;
            //remove the old attribs_and_params
            $this->attribs_and_params = array();
            //get the attribs
            $this->attribs_and_params["data"] = $token->attr['data'];
            $this->attribs_and_params["width"] = $token->attr['width'];
            $this->attribs_and_params["height"] = $token->attr['height'];

        }elseif($this->depth == 1 && $token->name == 'param'){
            //1st level param, store and delete
            if($token->attr['name'] == 'movie' || $token->attr['name'] == 'flashvars'){
                $this->attribs_and_params[$token->attr['name']] = $token->attr['value'];
            }
            $token = array();
        }elseif($this->depth > 0 && $token instanceof HTMLPurifier_Token_Start){
            //a nested start tag, increase depth

            $this->depth++;
        }elseif($this->depth > 0){
             //anything else, delete
             $token = array();
        }

        return;

    }

    public function handleEnd(&$token) {
        //if we are inside an object and hit an end tag, go to the corresponding start tag via rewind
        if($this->depth > 0 ){
            $this->depth--;
            //echo 'rewind'.(($this->inputIndex)-1);
            $this->from_a_rewind = 1;
            $this->rewind((($this->inputIndex)-1));
        }
        return;
    }

}

