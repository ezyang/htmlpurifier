<?php

/**
 * A "safe" iframe module. See SafeObject. This is a proprietary element.
 */
class HTMLPurifier_HTMLModule_SafeIframe extends HTMLPurifier_HTMLModule
{

    public $name = 'SafeIframe';

    public function setup($config) {

        $max = $config->get('HTML.MaxImgLength');
        $embed = $this->addElement(
            'iframe', 'Inline', 'Flow', 'Common',
            array(
                'src*' => 'URI#embedded',
                'width' => 'Pixels#' . $max,
                'height' => 'Pixels#' . $max,
                'name' => 'ID',
                'scrolling' => 'Enum#yes,no,auto',
                'frameborder' => 'Enum#0,1',
            )
        );

    }

}

// vim: et sw=4 sts=4
