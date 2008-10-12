<?php

class HTMLPurifier_HTMLModule_StandardFlash extends HTMLPurifier_HTMLModule
{

    public $name = 'StandardFlash';

    public function setup($config) {

        $object = $this->addElement(
            'object',
            'Inline',
            'Optional: param | Flow | #PCDATA',
            'Common'
        );

        $embed = $this->addElement(
            'embed', 'Inline', 'Empty', 'Common'
        );

         $param = $this->addElement('param', false, 'Empty', false
        );

        $this->info_injector[] = 'StandardFlash';

    }

}
