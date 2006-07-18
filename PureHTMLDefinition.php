<?php

class PureHTMLDefinition
{
    
    var $generator;
    var $info = array();
    var $info_closes_p = array(
        // these are all block elements: blocks aren't allowed in P
        'address'       => true,
        'blockquote'    => true,
        'dd'            => true,
        'dir'           => true,
        'div'           => true, 
        'dl'            => true,
        'dt'            => true,
        'h1'            => true,
        'h2'            => true,
        'h3'            => true,
        'h4'            => true, 
        'h5'            => true,
        'h6'            => true,
        'hr'            => true,
        'ol'            => true,
        'p'             => true,
        'pre'           => true, 
        'table'         => true,
        'ul'            => true
        );
    
    function PureHTMLDefinition() {
        $this->generator = new HTML_Generator();
    }
    
    function loadData() {
        // emulates the structure of the DTD
        
        // entities: prefixed with e_ and _ replaces .
        // we don't use an array because that complicates interpolation
        // strings are used instead of arrays because if you use arrays,
        // you have to do some hideous manipulation with array_merge()
        
        // these are condensed, remember, with bad stuff taken out
        
        // transforms: font, menu, dir, center
        
        $e_special_extra = 'img';
        $e_special_basic = 'br | span | bdo';
        $e_special = "$e_special_basic | $e_special_extra";
        $e_fontstyle_extra = 'big | small';
        $e_fontstyle_basic = 'tt | i | b | u | s | strike';
        $e_fontstyle = "$e_fontstyle_basic | $e_fontstyle_extra";
        $e_phrase_extra = 'sub | sup';
        $e_phrase_basic = 'em | strong | dfn | code | q | samp | kbd | var'.
          ' | cite | abbr | acronym';
        $e_phrase = "$e_phrase_basic | $e_phrase_extra";
        $e_inline_forms = ''; // humor the dtd
        $e_misc_inline = 'ins | del';
        $e_misc = "$e_misc_inline";
        $e_inline = "a | $e_special | $e_fontstyle | $e_phrase".
          " | $e_inline_forms";
        // note the casing
        $e_Inline = new HTMLDTD_ChildDef_Optional("#PCDATA | $e_inline".
          " | $e_misc_inline");
        $e_heading = 'h1|h2|h3|h4|h5|h6';
        $e_lists = 'ul | ol | dl';
        $e_blocktext = 'pre | hr | blockquote | address';
        $e_block = "p | $e_heading | div | $e_lists | $e_blocktext | table";
        $e_Flow = new HTMLDTD_ChildDef_Optional("#PCDATA | $e_block".
          " | $e_inline | $e_misc");
        $e_a_content = new HTMLDTD_ChildDef_Optional("#PCDATA | $e_special".
          " | $e_fontstyle | $e_phrase | $e_inline_forms | $e_misc_inline");
        $e_pre_content = new HTMLDTD_ChildDef_Optional("#PCDATA | a".
          " | $e_special_basic | $e_fontstyle_basic | $e_phrase_basic".
          " | $e_inline_forms | $e_misc_inline");
        $e_form_content = new HTMLDTD_ChildDef_Optional(''); //unused
        $e_form_button_content = new HTMLDTD_ChildDef_Optional(''); // unused
        
        $this->info['ins'] =
        $this->info['del'] = 
        $this->info['blockquote'] =
        $this->info['dd']  =
        $this->info['li']  =
        $this->info['div'] = new HTMLDTD_Element($e_Flow);
        
        $this->info['em']  =
        $this->info['strong'] =
        $this->info['dfn']  =
        $this->info['code'] =
        $this->info['samp'] =
        $this->info['kbd']  =
        $this->info['var']  =
        $this->info['code'] =
        $this->info['samp'] =
        $this->info['kbd']  =
        $this->info['var']  =
        $this->info['cite'] =
        $this->info['abbr'] =
        $this->info['acronym'] =
        $this->info['q']    =
        $this->info['sub']  =
        $this->info['tt']   =
        $this->info['sup']  =
        $this->info['i']    =
        $this->info['b']    =
        $this->info['big']  =
        $this->info['small'] =
        $this->info['u']    =
        $this->info['s']    =
        $this->info['strike'] =
        $this->info['bdo']  =
        $this->info['span'] =
        $this->info['dt']   =
        $this->info['p']    = 
        $this->info['h1']   = 
        $this->info['h2']   = 
        $this->info['h3']   = 
        $this->info['h4']   = 
        $this->info['h5']   = 
        $this->info['h6']   = new HTMLDTD_Element($e_Inline);
        
        $this->info['ol']   =
        $this->info['ul']   =
          new HTMLDTD_Element(
            new HTMLDTD_ChildDef_Required('li')
          );
        
        $this->info['dl']   =
          new HTMLDTD_Element(
            new HTMLDTD_ChildDef_Required('dt|dd')
          );
        $this->info['address'] =
          new HTMLDTD_Element(
            new HTMLDTD_ChildDef_Optional("#PCDATA | p | $e_inline".
              " | $e_misc_inline")
          );
        
        $this->info['img']  =
        $this->info['br']   =
        $this->info['hr']   = new HTMLDTD_Element(new HTMLDTD_ChildDef_Empty());
        
        $this->info['pre']  = new HTMLDTD_Element($e_pre_content);
        
        $this->info['a']    = new HTMLDTD_Element($e_a_content);
        
    }
    
    function purifyTokens($tokens) {
        if (empty($this->info)) $this->loadData();
        $tokens = $this->removeForeignElements($tokens);
        $tokens = $this->makeWellFormed($tokens);
        $tokens = $this->fixNesting($tokens);
        $tokens = $this->validateAttributes($tokens);
        return $tokens;
    }
    
    function removeForeignElements($tokens) {
        if (empty($this->info)) $this->loadData();
        $result = array();
        foreach($tokens as $token) {
            if (is_subclass_of($token, 'MF_Tag')) {
                if (!isset($this->info[$token->name])) {
                    // invalid tag, generate HTML and insert in
                    $token = new MF_Text(
                        $this->generator->generateFromToken($token)
                    );
                }
            } elseif ($token->type == 'comment') {
                // strip comments
                continue;
            } elseif ($token->type == 'text') {
            } else {
                continue;
            }
            $result[] = $token;
        }
        return $result;
    }
    
    function makeWellFormed($tokens) {
        if (empty($this->info)) $this->loadData();
        $result = array();
        $current_nesting = array();
        foreach ($tokens as $token) {
            if (!is_subclass_of($token, 'MF_Tag')) {
                $result[] = $token;
                continue;
            }
            $info = $this->info[$token->name]; // assumption but valid
            
            // test if it claims to be a start tag but is empty
            if ($info->child_def->type == 'empty' &&
                $token->type == 'start' ) {
                
                $result[] = new MF_EmptyTag($token->name, $token->attributes);
                continue;
            }
            
            // test if it claims to be empty but really is a start tag
            if ($info->child_def->type != 'empty' &&
                $token->type == 'empty' ) {
                
                $result[] = new MF_StartTag($token->name, $token->attributes);
                $result[] = new MF_EndTag($token->name);
                
                continue;
            }
            
            // automatically insert empty tags
            if ($token->type == 'empty') {
                $result[] = $token;
                continue;
            }
            
            // we give start tags precedence, so automatically accept unless...
            // it's one of those special cases
            if ($token->type == 'start') {
                
                // if there's a parent, check for special case
                if (!empty($current_nesting)) {
                    $current_parent = array_pop($current_nesting);
                    
                    // check if we're closing a P tag
                    if ($current_parent->name == 'p' &&
                        isset($this->info_closes_p[$token->name])
                        ) {
                        $result[] = new MF_EndTag('p');
                        $result[] = $token;
                        $current_nesting[] = $token;
                        continue;
                    }
                    
                    // check if we're closing a LI tag
                    if ($current_parent->name == 'li' &&
                        $token->name == 'li'
                        ) {
                        $result[] = new MF_EndTag('li');
                        $result[] = $token;
                        $current_nesting[] = $token;
                        continue;
                    }
                    
                    // this is more TIDY stuff
                    // we should also get some TABLE related code
                    // mismatched h#
                    
                    $current_nesting[] = $current_parent; // undo the pop
                }
                
                $result[] = $token;
                $current_nesting[] = $token;
                continue;
            }
            
            // sanity check
            if ($token->type != 'end') continue;
            
            // okay, we're dealing with a closing tag
            
            // make sure that we have something open
            if (empty($current_nesting)) {
                $result[] = new MF_Text(
                    $this->generator->generateFromToken($token)
                );
                continue;
            }
            
            // first, check for the simplest case: everything closes neatly
            
            // current_nesting is modified
            $current_parent = array_pop($current_nesting);
            if ($current_parent->name == $token->name) {
                $result[] = $token;
                continue;
            }
            
            // undo the array_pop
            $current_nesting[] = $current_parent;
            
            // okay, so we're trying to close the wrong tag
            
            // scroll back the entire nest, trying to find our tag
            // feature could be to specify how far you'd like to go
            $size = count($current_nesting);
            // -2 because -1 is the last element, but we already checked that
            $skipped_tags = false;
            for ($i = $size - 2; $i >= 0; $i--) {
                if ($current_nesting[$i]->name == $token->name) {
                    // current nesting is modified
                    $skipped_tags = array_splice($current_nesting, $i);
                    break;
                }
            }
            
            // we still didn't find the tag, so translate to text
            if ($skipped_tags === false) {
                $result[] = new MF_Text(
                    $this->generator->generateFromToken($token)
                );
                continue;
            }
            
            // okay, we found it, close all the skipped tags
            // note that skipped tags contains the element we need closed
            $size = count($skipped_tags);
            for ($i = $size - 1; $i >= 0; $i--) {
                $result[] = new MF_EndTag($skipped_tags[$i]->name);
            }
            
            // done!
            
        }
        
        // we're at the end now, fix all still unclosed tags
        
        if (!empty($current_nesting)) {
            $size = count($current_nesting);
            for ($i = $size - 1; $i >= 0; $i--) {
                $result[] = new MF_EndTag($current_nesting[$i]->name);
            }
        }
        
        return $result;
    }
    
    function fixNesting($tokens) {
        if (empty($this->info)) $this->loadData();
        
        /*$to_next_node = 0; // defines how much further to scroll to get
                           // to next node.
        
        for ($i = 0, $size = count($tokens) ; $i < $size; $i += $to_next_node) {
            
            // scroll to the end of this node, and report number
            for ($j = $i, $depth = 0; ; $j++) {
            }
        }*/
        
    }
    
    function validateAttributes($tokens) {
        if (empty($this->info)) $this->loadData();
        
    }
    
}

class HTMLDTD_Element
{
    
    var $child_def;
    var $attr_def = array();
    
    function HTMLDTD_Element($child_def, $attr_def = array()) {
        $this->child_def = $child_def;
        $this->attr_def  = $attr_def;
    }
    
}

// HTMLDTD_ChildDef and inheritance have three types of output:
// true = leave nodes as is
// false = delete parent node and all children
// array(...) = replace children nodes with these

// this is the hardest one to implement. We'll use fancy regexp tricks
// right now, we only expect it to return TRUE or FALSE (it won't attempt
// to fix the tree)

// we may end up writing custom code for each HTML case
// in order to make it self correcting
class HTMLDTD_ChildDef
{
    var $dtd_regex;
    var $_pcre_regex;
    function HTMLDTD_ChildDef($dtd_regex) {
        $this->dtd_regex = $dtd_regex;
        $this->_compileRegex();
    }
    function _compileRegex() {
        $raw = str_replace(' ', '', $this->dtd_regex);
        if ($raw{0} != '(') {
            $raw = "($raw)";
        }
        $reg = str_replace(',', ',?', $raw);
        $reg = preg_replace('/([#a-zA-Z0-9_.-]+)/', '(,?\\0)', $reg);
        $this->_pcre_regex = $reg;
    }
    function validateChildren($tokens_of_children) {
        $list_of_children = '';
        $nesting = 0; // depth into the nest
        foreach ($tokens_of_children as $token) {
            if (!empty($token->is_whitespace)) continue;
            
            $is_child = ($nesting == 0); // direct
            
            if ($token->type == 'start') {
                $nesting++;
            } elseif ($token->type == 'end') {
                $nesting--;
            }
            
            if ($is_child) {
                $list_of_children .= $token->name . ',';
            }
        }
        $list_of_children = rtrim($list_of_children, ',');
        
        $okay =
            preg_match(
                '/^'.$this->_pcre_regex.'$/',
                $list_of_children
            );
        
        return (bool) $okay;
    }
}
class HTMLDTD_ChildDef_Simple extends HTMLDTD_ChildDef
{
    var $elements = array();
    function HTMLDTD_ChildDef_Simple($elements) {
        if (is_string($elements)) {
            $elements = str_replace(' ', '', $elements);
            $elements = explode('|', $elements);
        }
        $elements = array_flip($elements);
        foreach ($elements as $i => $x) $elements[$i] = true;
        $this->elements = $elements;
        $this->gen = new HTML_Generator();
    }
    function validateChildren() {
        trigger_error('Cannot call abstract function!', E_USER_ERROR);
    }
}
class HTMLDTD_ChildDef_Required extends HTMLDTD_ChildDef_Simple
{
    var $type = 'required';
    function validateChildren($tokens_of_children) {
        // if there are no tokens, delete parent node
        if (empty($tokens_of_children)) return false;
        
        // the new set of children
        $result = array();
        
        // current depth into the nest
        $nesting = 0;
        
        // whether or not we're deleting a node
        $is_deleting = false;
        
        // whether or not parsed character data is allowed
        // this controls whether or not we silently drop a tag
        // or generate escaped HTML from it
        $pcdata_allowed = isset($this->elements['#PCDATA']);
        
        // a little sanity check to make sure it's not ALL whitespace
        $all_whitespace = true;
        
        foreach ($tokens_of_children as $token) {
            if (!empty($token->is_whitespace)) {
                $result[] = $token;
                continue;
            }
            $all_whitespace = false; // phew, we're not talking about whitespace
            
            $is_child = ($nesting == 0);
            
            if ($token->type == 'start') {
                $nesting++;
            } elseif ($token->type == 'end') {
                $nesting--;
            }
            
            if ($is_child) {
                $is_deleting = false;
                if (!isset($this->elements[$token->name])) {
                    $is_deleting = true;
                    if ($pcdata_allowed) {
                        $result[] = new MF_Text(
                            $this->gen->generateFromToken($token)
                        );
                    }
                    continue;
                }
            }
            if (!$is_deleting) {
                $result[] = $token;
            } elseif ($pcdata_allowed) {
                $result[] = new MF_Text($this->gen->generateFromToken($token));
            } else {
                // drop silently
            }
        }
        if (empty($result)) return false;
        if ($all_whitespace) return false;
        if ($tokens_of_children == $result) return true;
        return $result;
    }
}

// only altered behavior is that it returns an empty array
// instead of a false (to delete the node)
class HTMLDTD_ChildDef_Optional extends HTMLDTD_ChildDef_Required
{
    var $type = 'optional';
    function validateChildren($tokens_of_children) {
        $result = parent::validateChildren($tokens_of_children);
        if ($result === false) return array();
        return $result;
    }
}

// placeholder
class HTMLDTD_ChildDef_Empty extends HTMLDTD_ChildDef
{
    var $type = 'empty';
    function HTMLDTD_ChildDef_Empty() {}
    function validateChildren() {
        return false;
    }
}

class HTMLDTD_AttrDef
{
    var $def;
    function HTMLDTD_AttrDef($def) {
        $this->def = $def;
    }
}

?>