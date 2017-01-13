<?php

/**
 * Module adds the noopener attribute transformation to a tags.  It
 * is enabled by HTML.Noopener
 */
class HTMLPurifier_HTMLModule_Noopener extends HTMLPurifier_HTMLModule
{

    /**
     * @type string
     */
    public $name = 'Noopener';

    /**
     * @param HTMLPurifier_Config $config
     */
    public function setup($config)
    {
        $a = $this->addBlankElement('a');
        $a->attr_transform_post[] = new HTMLPurifier_AttrTransform_Noopener();
    }
}

// vim: et sw=4 sts=4
