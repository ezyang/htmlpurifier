<?php

/**
 * A "safe" iframe module. See SafeObject. This is a proprietary element.
 */
class HTMLPurifier_HTMLModule_SafeIframe extends HTMLPurifier_HTMLModule
{

    public $name = 'SafeIframe';

    public function setup($config) {
        $max = $config->get('HTML.MaxImgLength');

        $iframe = $this->addElement(
            'iframe', 'Inline', 'Flow', 'Common',
            array(
                'src*' => 'URI#embedded',
                // According to the spec, it's Length, but percents can
                // be abused, so we allow only Pixels.
                'width' => 'Pixels#' . $max,
                'height' => 'Pixels#' . $max,
                'name' => 'ID',
                'scrolling' => 'Enum#yes,no,auto',
                'frameborder' => 'Enum#0,1',
            )
        );
        if ($max === null || $config->get('HTML.Trusted')) {
            $iframe->attr['height'] =
            $iframe->attr['width'] = 'Length';
        }

        $noframes = $this->addElement(
            'noframes', 'Inline', 'Flow', 'Common'
        );

    }

}

// vim: et sw=4 sts=4
