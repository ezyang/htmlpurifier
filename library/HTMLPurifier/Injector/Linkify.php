<?php

require_once 'HTMLPurifier/Injector.php';

/**
 * Injector that converts http, https and ftp text URLs to actual links.
 */
class HTMLPurifier_Injector_Linkify extends HTMLPurifier_Injector
{
    
    function handleText(&$token, $config, &$context) {
        $current_nesting =& $context->get('CurrentNesting');
        // this snippet could be factored out
        $definition = $config->getHTMLDefinition();
        if (!empty($current_nesting)) {
            $parent_token = array_pop($current_nesting);
            $current_nesting[] = $parent_token;
            $parent = $definition->info[$parent_token->name];
        } else {
            $parent = $definition->info_parent_def;
        }
        if (!isset($parent->child->elements['a']) || isset($parent->excludes['a'])) {
            // parent element does not allow link elements, don't bother
            return;
        }
        if (strpos($token->data, '://') === false) {
            // our really quick heuristic failed, abort
            // this may not work so well if we want to match things like
            // "google.com"
            return;
        }
        
        // there is/are URL(s). Let's split the string:
        $bits = preg_split('#((?:https?|ftp)://[^\s\'"<>()]+)#S', $token->data, -1, PREG_SPLIT_DELIM_CAPTURE);
        
        $token = array();
        
        // $i = index
        // $c = count
        // $l = is link
        for ($i = 0, $c = count($bits), $l = false; $i < $c; $i++, $l = !$l) {
            if (!$l) {
                if ($bits[$i] === '') continue;
                $token[] = new HTMLPurifier_Token_Text($bits[$i]);
            } else {
                $token[] = new HTMLPurifier_Token_Start('a', array('href' => $bits[$i]));
                $token[] = new HTMLPurifier_Token_Text($bits[$i]);
                $token[] = new HTMLPurifier_Token_End('a');
            }
        }
        
    }
    
}

?>