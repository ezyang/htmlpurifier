<?php

// must be called POST validation

/**
 * Adds rel="noreferrer" to all outbound links.  This transform is
 * only attached if HTML.Noreferrer is TRUE.
 */
class HTMLPurifier_AttrTransform_Noreferrer extends HTMLPurifier_AttrTransform
{
    /**
     * @type HTMLPurifier_URIParser
     */
    private $parser;

    public function __construct()
    {
        $this->parser = new HTMLPurifier_URIParser();
    }

    /**
     * @param array $attr
     * @param HTMLPurifier_Config $config
     * @param HTMLPurifier_Context $context
     * @return array
     */
    public function transform($attr, $config, $context)
    {
        // Nothing to do If we already have noreferrer in the rel attribute
        if (!empty($attr['rel']) && substr($attr['rel'], 'noreferrer') !== false) {
            return $attr;
        }

        // If _blank target attribute exists, add rel=noreferrer
        if (!empty($attr['target']) && $attr['target'] == '_blank') {
            $attr['rel'] = !empty($attr['rel']) ? $attr['rel'] . ' noreferrer' : 'noreferrer';
        }

        return $attr;
    }
}

