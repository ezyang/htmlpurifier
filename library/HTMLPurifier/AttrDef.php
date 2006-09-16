<?php

require_once 'HTMLPurifier/AttrContext.php';

/**
 * Base class for all validating attribute definitions.
 * 
 * This family of classes forms the core for not only HTML attribute validation,
 * but also any sort of string that needs to be validated or cleaned (which
 * means CSS properties and composite definitions are defined here too).  
 * Besides defining (through code) what precisely makes the string valid,
 * subclasses are also responsible for cleaning the code if possible.
 */

class HTMLPurifier_AttrDef
{
    
    /**
     * Tells us whether or not an HTML attribute is minimized. Only the
     * boolean attribute vapourware would use this.
     */
    var $minimized = false;
    
    /**
     * Abstract function defined for functions that validate and clean strings.
     * 
     * This function forms the basis for all the subclasses: they must
     * define this method.
     * 
     * @public
     * @param $string String to be validated and cleaned.
     * @param $config Mandatory HTMLPurifier_Config object.
     * @param $context Mandatory HTMLPurifier_AttrContext object.
     */
    function validate($string, $config, &$context) {
        trigger_error('Cannot call abstract function', E_USER_ERROR);
    }
    
    /**
     * Convenience method that parses a string as if it were CDATA.
     * 
     * This method process a string in the manner specified at
     * <http://www.w3.org/TR/html4/types.html#h-6.2> by removing
     * leading and trailing whitespace, ignoring line feeds, and replacing
     * carriage returns and tabs with spaces.  While most useful for HTML
     * attributes specified as CDATA, it can also be applied to most CSS
     * values.
     * 
     * @note This method is not entirely standards compliant, as trim() removes
     *       more types of whitespace than specified in the spec. In practice,
     *       this is rarely a problem.
     * 
     * @public
     */
    function parseCDATA($string) {
        $string = trim($string);
        $string = str_replace("\n", '', $string);
        $string = str_replace(array("\r", "\t"), ' ', $string);
        return $string;
    }
}

?>