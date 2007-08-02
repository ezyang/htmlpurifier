<?php

require_once 'HTMLPurifier/Definition.php';
require_once 'HTMLPurifier/URIFilter.php';

require_once 'HTMLPurifier/URIFilter/DisableExternal.php';
require_once 'HTMLPurifier/URIFilter/DisableExternalResources.php';

HTMLPurifier_ConfigSchema::define(
    'URI', 'DefinitionRev', 1, 'int', '
<p>
    Revision identifier for your custom definition. See
    %HTML.DefinitionRev for details. This directive has been available
    since 2.1.0.
</p>
');

class HTMLPurifier_URIDefinition extends HTMLPurifier_Definition
{
    
    var $type = 'URI';
    var $filters = array();
    var $registeredFilters = array();
    
    function HTMLPurifier_URIDefinition() {
        $this->registerFilter(new HTMLPurifier_URIFilter_DisableExternal());
        $this->registerFilter(new HTMLPurifier_URIFilter_DisableExternalResources());
    }
    
    function registerFilter($filter) {
        $this->registeredFilters[$filter->name] = $filter;
    }
    
    function doSetup($config) {
        foreach ($this->registeredFilters as $name => $filter) {
            $conf = $config->get('URI', $name);
            if ($conf !== false && $conf !== null) {
                $this->filters[$name] = $filter;
            }
        }
        foreach ($this->filters as $n => $x) $this->filters[$n]->prepare($config);
        unset($this->registeredFilters);
    }
    
    function filter(&$uri, $config, &$context) {
        foreach ($this->filters as $name => $x) {
            $result = $this->filters[$name]->filter($uri, $config, $context);
            if (!$result) return false;
        }
        return true;
    }
    
}
