<?php

require_once 'HTMLPurifier/Filter.php';

/**
 * This filter extracts <style> blocks from input HTML, cleans them up
 * using CSSTidy, and then places them in $purifier->context->get('StyleBlocks')
 * so they can be used elsewhere in the document.
 * @note See tests/HTMLPurifier/Filter/ExtractStyleBlocksTest.php
 * @todo Allow for selectors to be munged/checked
 * @todo Expose CSSTidy configuration so that custom changes can be made
 */
class HTMLPurifier_Filter_ExtractStyleBlocks extends HTMLPurifier_Filter
{
    
    public $name = 'ExtractStyleBlocks';
    private $_styleMatches = array();
    private $_tidy, $_disableCharacterEscaping;
    
    /**
     * @param $tidy Instance of csstidy to use, false to turn off cleaning,
     *              and null to automatically instantiate
     * @param $disable_character_escaping Whether or not to stop munging
     *              <, > and &. This can be set to true if the CSS will
     *              be placed in an external style and not inline.
     */
    public function __construct($tidy = null, $disable_character_escaping = false) {
        if ($tidy === null) $tidy = new csstidy();
        $this->_tidy = $tidy;
        $this->_disableCharacterEscaping = $disable_character_escaping;
    }
    
    /**
     * Save the contents of CSS blocks to style matches
     * @param $matches preg_replace style $matches array
     */
    protected function styleCallback($matches) {
        $this->_styleMatches[] = $matches[1];
    }
    
    /**
     * Removes inline <style> tags from HTML, saves them for later use
     * @todo Extend to indicate non-text/css style blocks
     */
    public function preFilter($html, $config, &$context) {
        $html = preg_replace_callback('#<style(?:\s.*)?>(.+)</style>#isU', array($this, 'styleCallback'), $html);
        $style_blocks = $this->_styleMatches;
        $this->_styleMatches = array(); // reset
        $context->register('StyleBlocks', $style_blocks); // $context must not be reused
        if ($this->_tidy) {
            foreach ($style_blocks as &$style) {
                $style = $this->cleanCSS($style, $config, $context);
            }
        }
        return $html;
    }
    
    /**
     * Takes CSS (the stuff found in <style>) and cleans it.
     * @warning Requires CSSTidy <http://csstidy.sourceforge.net/>
     * @param $css CSS styling to clean
     * @param $config Instance of HTMLPurifier_Config
     * @param $context Instance of HTMLPurifier_Context
     * @return Cleaned CSS
     */
    public function cleanCSS($css, $config, &$context) {
        $this->_tidy->parse($css);
        $css_definition = $config->getDefinition('CSS');
        foreach ($this->_tidy->css as &$decls) {
            // $decls are all CSS declarations inside an @ selector
            foreach ($decls as &$style) {
                foreach ($style as $name => $value) {
                    if (!isset($css_definition->info[$name])) {
                        unset($style[$name]);
                        continue;
                    }
                    $def = $css_definition->info[$name];
                    $ret = $def->validate($value, $config, $context);
                    if ($ret === false) unset($style[$name]);
                    else $style[$name] = $ret;
                }
            }
        }
        // remove stuff that shouldn't be used, could be reenabled
        // after security risks are analyzed
        $this->_tidy->import = array();
        $this->_tidy->charset = null;
        $this->_tidy->namespace = null;
        $printer = new csstidy_print($this->_tidy);
        $css = $printer->plain();
        // we are going to escape any special characters <>& to ensure
        // that no funny business occurs (i.e. </style> in a font-family prop).
        if (!$this->_disableCharacterEscaping) {
            $css = str_replace(
                array('<',    '>',    '&'),
                array('\3C ', '\3E ', '\26 '),
                $css
            );
        }
        return $css;
    }
    
}

