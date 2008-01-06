<?php

require_once 'HTMLPurifier/Filter.php';

HTMLPurifier_ConfigSchema::define(
    'Filter', 'ExtractStyleBlocksEscaping', true, 'bool', '
<p>
  Whether or not to escape the dangerous characters &lt;, &gt; and &amp;
  as \3C, \3E and \26, respectively. This is can be safely set to false
  if the contents of StyleBlocks will be placed in an external stylesheet,
  where there is no risk of it being interpreted as HTML. This directive
  has been available since 3.0.0.
</p>
'
);

HTMLPurifier_ConfigSchema::define(
    'Filter', 'ExtractStyleBlocksScope', null, 'string/null', '
<p>
  If you would like users to be able to define external stylesheets, but
  only allow them to specify CSS declarations for a specific node and
  prevent them from fiddling with other elements, use this directive.
  It accepts any valid CSS selector, and will prepend this to any
  CSS declaration extracted from the document. For example, if this
  directive is set to <code>#user-content</code> and a user uses the
  selector <code>a:hover</code>, the final selector will be
  <code>#user-content a:hover</code>.
</p>
<p>
  The comma shorthand may be used; consider the above example, with
  <code>#user-content, #user-content2</code>, the final selector will
  be <code>#user-content a:hover, #user-content2 a:hover</code>.
</p>
<p>
  <strong>Warning:</strong> It is possible for users to bypass this measure
  using a naughty + selector. This is a bug in CSS Tidy 1.3, not HTML
  Purifier, and I am working to get it fixed. Until then, HTML Purifier
  performs a basic check to prevent this.
</p>
<p>
  This directive has been available since 3.0.0.
</p>
'
);

/**
 * This filter extracts <style> blocks from input HTML, cleans them up
 * using CSSTidy, and then places them in $purifier->context->get('StyleBlocks')
 * so they can be used elsewhere in the document.
 * 
 * @note
 *      See tests/HTMLPurifier/Filter/ExtractStyleBlocksTest.php for
 *      sample usage.
 * 
 * @note
 *      This filter can also be used on stylesheets not included in the
 *      document--something purists would probably prefer. Just directly
 *      call HTMLPurifier_Filter_ExtractStyleBlocks->cleanCSS()
 */
class HTMLPurifier_Filter_ExtractStyleBlocks extends HTMLPurifier_Filter
{
    
    public $name = 'ExtractStyleBlocks';
    private $_styleMatches = array();
    private $_tidy;
    
    /**
     * @param $tidy
     *      Instance of csstidy to use, false to turn off cleaning,
     *      and null to automatically instantiate
     */
    public function __construct($tidy = null) {
        if ($tidy === null) $tidy = new csstidy();
        $this->_tidy = $tidy;
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
    public function preFilter($html, $config, $context) {
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
     * @param $css     CSS styling to clean
     * @param $config  Instance of HTMLPurifier_Config
     * @param $context Instance of HTMLPurifier_Context
     * @return Cleaned CSS
     */
    public function cleanCSS($css, $config, $context) {
        // prepare scope
        $scope = $config->get('Filter', 'ExtractStyleBlocksScope');
        if ($scope !== null) {
            $scopes = array_map('trim', explode(',', $scope));
        } else {
            $scopes = array();
        }
        $this->_tidy->parse($css);
        $css_definition = $config->getDefinition('CSS');
        foreach ($this->_tidy->css as $k => $decls) {
            // $decls are all CSS declarations inside an @ selector
            $new_decls = array();
            foreach ($decls as $selector => $style) {
                $selector = trim($selector);
                if ($selector === '') continue; // should not happen
                if ($selector[0] === '+') {
                    if ($selector !== '' && $selector[0] === '+') continue;
                }
                if (!empty($scopes)) {
                    $new_selector = array(); // because multiple ones are possible
                    $selectors = array_map('trim', explode(',', $selector));
                    foreach ($scopes as $s1) {
                        foreach ($selectors as $s2) {
                            $new_selector[] = "$s1 $s2";
                        }
                    }
                    $selector = implode(', ', $new_selector); // now it's a string
                }
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
                $new_decls[$selector] = $style;
            }
            $this->_tidy->css[$k] = $new_decls;
        }
        // remove stuff that shouldn't be used, could be reenabled
        // after security risks are analyzed
        $this->_tidy->import = array();
        $this->_tidy->charset = null;
        $this->_tidy->namespace = null;
        $css = $this->_tidy->print->plain();
        // we are going to escape any special characters <>& to ensure
        // that no funny business occurs (i.e. </style> in a font-family prop).
        if ($config->get('Filter', 'ExtractStyleBlocksEscaping')) {
            $css = str_replace(
                array('<',    '>',    '&'),
                array('\3C ', '\3E ', '\26 '),
                $css
            );
        }
        return $css;
    }
    
}

