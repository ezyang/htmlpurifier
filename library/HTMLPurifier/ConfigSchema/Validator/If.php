<?php

/**
 * If a validator passes, run another validator.
 */
class HTMLPurifier_ConfigSchema_Validator_If extends HTMLPurifier_ConfigSchema_Validator
{
    
    protected $condition;
    protected $then;
    protected $else;
    
    public function __construct($cond = null) {
        $this->setCondition($cond);
    }
    
    /**
     * @param $validator Validator to run as a condition. Exceptions thrown by it
     *        do not bubble up.
     */
    public function setCondition($validator) {
        $this->condition = $validator;
    }
    
    /**
     * @param $validator Validator to run if condition is true
     */
    public function setThen($validator) {
        $this->then = $validator;
    }
    
    /**
     * @param $validator Validator to run if condition is false
     */
    public function setElse($validator) {
        $this->else = $validator;
    }
    
    public function validate(&$arr, $interchange) {
        try {
            $this->condition->validate($arr, $interchange);
        } catch (HTMLPurifier_ConfigSchema_Exception $e) {
            if ($this->else) $this->else->validate($arr, $interchange);
            return;
        }
        if ($this->then) $this->then->validate($arr, $interchange);
    }
    
}
