<?php

require_once 'HTMLPurifier/Lexer.php';

HTMLPurifier_ConfigSchema::define(
    'Output', 'EnableRedundantUTF8Cleaning', false, 'bool',
    'When true, HTMLPurifier_Generator will also check all strings it '.
    'escapes for UTF-8 well-formedness as a defense in depth measure. '.
    'This could cause a considerable performance impact, and is not '.
    'strictly necessary due to the fact that the Lexers should have '.
    'ensured that all the UTF-8 strings were well-formed.  Note that '.
    'the configuration value is only read at the beginning of '.
    'generateFromTokens.'
);
HTMLPurifier_ConfigSchema::defineAlias('Core', 'CleanUTF8DuringGeneration', 'Output', 'EnableRedundantUTF8Cleaning');

HTMLPurifier_ConfigSchema::define(
    'Output', 'CommentScriptContents', true, 'bool',
    'Determines whether or not HTML Purifier should attempt to fix up '.
    'the contents of script tags for legacy browsers with comments. This '.
    'directive was available since 1.7.'
);
HTMLPurifier_ConfigSchema::defineAlias('Core', 'CommentScriptContents', 'Output', 'CommentScriptContents');

// extension constraints could be factored into ConfigSchema
HTMLPurifier_ConfigSchema::define(
    'Output', 'TidyFormat', false, 'bool', <<<HTML
<p>
    Determines whether or not to run Tidy on the final output for pretty 
    formatting reasons, such as indentation and wrap.
</p>
<p>
    This can greatly improve readability for editors who are hand-editing
    the HTML, but is by no means necessary as HTML Purifier has already
    fixed all major errors the HTML may have had. Tidy is a non-default
    extension, and this directive will silently fail if Tidy is not
    available.
</p>
<p>
    If you are looking to make the overall look of your page's source
    better, I recommend running Tidy on the entire page rather than just
    user-content (after all, the indentation relative to the containing
    blocks will be incorrect).
</p>
<p>
    This directive was available since 1.1.1.
</p>
HTML
);
HTMLPurifier_ConfigSchema::defineAlias('Core', 'TidyFormat', 'Output', 'TidyFormat');

/**
 * Generates HTML from tokens.
 * @todo Create a configuration-wide instance that all objects retrieve
 */
class HTMLPurifier_Generator
{
    
    /**
     * Bool cache of %Output.EnableRedundantUTF8Cleaning
     * @private
     */
    var $_clean_utf8 = false;
    
    /**
     * Bool cache of %HTML.XHTML
     * @private
     */
    var $_xhtml = true;
    
    /**
     * Bool cache of %Output.CommentScriptContents
     * @private
     */
    var $_scriptFix = false;
    
    /**
     * Cache of HTMLDefinition
     * @private
     */
    var $_def;
    
    /**
     * Generates HTML from an array of tokens.
     * @param $tokens Array of HTMLPurifier_Token
     * @param $config HTMLPurifier_Config object
     * @return Generated HTML
     */
    function generateFromTokens($tokens, $config, &$context) {
        $html = '';
        if (!$config) $config = HTMLPurifier_Config::createDefault();
        $this->_clean_utf8  = $config->get('Output', 'EnableRedundantUTF8Cleaning');
        $this->_scriptFix   = $config->get('Output', 'CommentScriptContents');
        
        $doctype = $config->getDoctype();
        $this->_xhtml = $doctype->xml;
        
        $this->_def = $config->getHTMLDefinition();
        
        if (!$tokens) return '';
        for ($i = 0, $size = count($tokens); $i < $size; $i++) {
            if ($this->_scriptFix && $tokens[$i]->name === 'script') {
                // script special case
                $html .= $this->generateFromToken($tokens[$i++]);
                $html .= $this->generateScriptFromToken($tokens[$i++]);
                while ($tokens[$i]->name != 'script') {
                    $html .= $this->generateScriptFromToken($tokens[$i++]);
                }
            }
            $html .= $this->generateFromToken($tokens[$i]);
        }
        if ($config->get('Output', 'TidyFormat') && extension_loaded('tidy')) {
            
            $tidy_options = array(
               'indent'=> true,
               'output-xhtml' => $this->_xhtml,
               'show-body-only' => true,
               'indent-spaces' => 2,
               'wrap' => 68,
            );
            if (version_compare(PHP_VERSION, '5', '<')) {
                tidy_set_encoding('utf8');
                foreach ($tidy_options as $key => $value) {
                    tidy_setopt($key, $value);
                }
                tidy_parse_string($html);
                tidy_clean_repair();
                $html = tidy_get_output();
            } else {
                $tidy = new Tidy;
                $tidy->parseString($html, $tidy_options, 'utf8');
                $tidy->cleanRepair();
                $html = (string) $tidy;
            }
        }
        return $html;
    }
    
    /**
     * Generates HTML from a single token.
     * @param $token HTMLPurifier_Token object.
     * @return Generated HTML
     */
    function generateFromToken($token) {
        if (!isset($token->type)) return '';
        if ($token->type == 'start') {
            $attr = $this->generateAttributes($token->attr, $token->name);
            return '<' . $token->name . ($attr ? ' ' : '') . $attr . '>';
            
        } elseif ($token->type == 'end') {
            return '</' . $token->name . '>';
            
        } elseif ($token->type == 'empty') {
            $attr = $this->generateAttributes($token->attr, $token->name);
             return '<' . $token->name . ($attr ? ' ' : '') . $attr .
                ( $this->_xhtml ? ' /': '' )
                . '>';
            
        } elseif ($token->type == 'text') {
            return $this->escape($token->data);
            
        } else {
            return '';
            
        }
    }
    
    /**
     * Special case processor for the contents of script tags
     * @warning This runs into problems if there's already a literal
     *          --> somewhere inside the script contents.
     */
    function generateScriptFromToken($token) {
        if (!$token->type == 'text') return $this->generateFromToken($token);
        return '<!--' . PHP_EOL . $token->data . PHP_EOL . '// -->';
        // more advanced version:
        // return '<!--//--><![CDATA[//><!--' . PHP_EOL . $token->data . PHP_EOL . '//--><!]]>';
    }
    
    /**
     * Generates attribute declarations from attribute array.
     * @param $assoc_array_of_attributes Attribute array
     * @return Generate HTML fragment for insertion.
     */
    function generateAttributes($assoc_array_of_attributes, $element) {
        $html = '';
        foreach ($assoc_array_of_attributes as $key => $value) {
            if (!$this->_xhtml) {
                // remove namespaced attributes
                if (strpos($key, ':') !== false) continue;
                if (!empty($this->_def->info[$element]->attr[$key]->minimized)) {
                    $html .= $key . ' ';
                    continue;
                }
            }
            $html .= $key.'="'.$this->escape($value).'" ';
        }
        return rtrim($html);
    }
    
    /**
     * Escapes raw text data.
     * @param $string String data to escape for HTML.
     * @return String escaped data.
     */
    function escape($string) {
        if ($this->_clean_utf8) $string = HTMLPurifier_Lexer::cleanUTF8($string);
        return htmlspecialchars($string, ENT_COMPAT, 'UTF-8');
    }
    
}

?>