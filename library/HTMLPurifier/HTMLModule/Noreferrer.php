<?php

/**
 * Module adds the noreferrer attribute transformation to a tags.  It
 * is enabled by HTML.Noreferrer
 */
class HTMLPurifier_HTMLModule_Noreferrer extends HTMLPurifier_HTMLModule
{
    /**
     * @type string
     */
    public $name = 'Noreferrer';

    /**
     * @param HTMLPurifier_Config $config
     */
    public function setup($config) {
        $a = $this->addBlankElement('a');
        $a->attr_transform_post[] = new HTMLPurifier_AttrTransform_Noreferrer();
    }
}