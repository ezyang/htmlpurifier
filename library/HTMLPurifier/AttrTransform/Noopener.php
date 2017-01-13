<?php

// must be called POST validation

/**
 * Adds rel="noopener" to all outbound links.  This transform is
 * only attached if Attr.Noopener is TRUE.
 */
class HTMLPurifier_AttrTransform_Noopener extends HTMLPurifier_AttrTransform
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
        if (!isset($attr['href'])) {
            return $attr;
        }

        // XXX Kind of inefficient
        $url = $this->parser->parse($attr['href']);
        $scheme = $url->getSchemeObj($config, $context);

        if ($scheme->browsable && !$url->isLocal($config, $context)) {
            if (isset($attr['rel'])) {
                $rels = explode(' ', $attr['rel']);
                if (!in_array('noopener', $rels)) {
                    $rels[] = 'noopener';
                }
                $attr['rel'] = implode(' ', $rels);
            } else {
                $attr['rel'] = 'noopener';
            }
        }
        return $attr;
    }
}

// vim: et sw=4 sts=4
