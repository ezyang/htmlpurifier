<?php

class ConfigDoc_DOM_Document extends DOMDocument
{
    /**
     * Register our classes
     */
    public function __construct($version = "1.0", $encoding = "UTF-8") {
        parent::__construct($version, $encoding);
        parent::registerNodeClass('DOMDocument', 'ConfigDoc_DOM_Document');
        parent::registerNodeClass('DOMElement',  'ConfigDoc_DOM_Element');
    }
}
