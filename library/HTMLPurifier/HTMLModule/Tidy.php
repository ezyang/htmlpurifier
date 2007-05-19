<?php

require_once 'HTMLPurifier/HTMLModule.php';

HTMLPurifier_ConfigSchema::define(
    'HTML', 'TidyLevel', 'medium', 'string', '
<p>General level of cleanliness the Tidy module should enforce.
There are four allowed values:</p>
<dl>
    <dt>none</dt>
    <dd>No extra tidying should be done</dd>
    <dt>light</dt>
    <dd>Only fix elements that would be discarded otherwise due to
    lack of support in doctype</dd>
    <dt>medium</dt>
    <dd>Enforce best practices</dd>
    <dt>heavy</dt>
    <dd>Transform all deprecated elements and attributes to standards
    compliant equivalents</dd>
</dl>
<p>This directive has been available since 1.7.0</p>
' );
HTMLPurifier_ConfigSchema::defineAllowedValues(
    'HTML', 'TidyLevel', array('none', 'light', 'medium', 'heavy')
);

HTMLPurifier_ConfigSchema::define(
    'HTML', 'TidyAdd', array(), 'list', '
Fixes to add to the default set of Tidy fixes as per your level. This
directive has been available since 1.7.0.
' );

HTMLPurifier_ConfigSchema::define(
    'HTML', 'TidyRemove', array(), 'list', '
Fixes to remove from the default set of Tidy fixes as per your level. This
directive has been available since 1.7.0.
' );

/**
 * Abstract class for a set of proprietary modules that clean up (tidy)
 * poorly written HTML.
 */
class HTMLPurifier_HTMLModule_Tidy extends HTMLPurifier_HTMLModule
{
    
    /**
     * List of supported levels. Index zero is a special case "no fixes"
     * level.
     */
    var $levels = array(0 => 'none', 'light', 'medium', 'heavy');
    
    /**
     * Lists of fixes used by getFixesForLevel(). Format is:
     *      HTMLModule_Tidy->fixesForLevel[$level] = array('fix-1', 'fix-2');
     */
    var $fixesForLevel = array(
        'light'  => array(),
        'medium' => array(),
        'heavy'  => array()
    );
    
    /**
     * Lazy load constructs the module by determining the necessary
     * fixes to create and then delegating to the populate() function.
     * @todo Wildcard matching and error reporting when an added or
     *       subtracted fix has no effect.
     */
    function construct($config) {
        $level = $config->get('HTML', 'TidyLevel');
        $fixes = $this->getFixesForLevel($level);
        
        $add_fixes = $config->get('HTML', 'TidyAdd');
        foreach ($add_fixes as $fix) {
            $fixes[$fix] = true;
        }
        
        $remove_fixes = $config->get('HTML', 'TidyRemove');
        foreach ($remove_fixes as $fix) {
            unset($fixes[$fix]);
        }
        
        $this->populate($fixes);
    }
    
    /**
     * Retrieves all fixes per a level, returning fixes for that specific
     * level as well as all levels below it.
     * @param $level String level identifier, see $levels for valid values
     * @return Lookup up table of fixes
     */
    function getFixesForLevel($level) {
        if ($level == $this->levels[0]) {
            return array();
        }
        $activated_levels = array();
        for ($i = 1, $c = count($this->levels); $i < $c; $i++) {
            $activated_levels[] = $this->levels[$i];
            if ($this->levels[$i] == $level) break;
        }
        if ($i == $c) {
            trigger_error(
                'Tidy level ' . htmlspecialchars($level) . ' not recognized',
                E_USER_WARNING
            );
            return array();
        }
        $ret = array();
        foreach ($activated_levels as $level) {
            foreach ($this->fixesForLevel[$level] as $fix) {
                $ret[$fix] = true;
            }
        }
        return $ret;
    }
    
    /**
     * Populates the module with transforms and other special-case code
     * based on a list of fixes passed to it
     * @abstract
     * @param $lookup Lookup table of fixes to activate
     */
    function populate($lookup) {}
    
}

?>