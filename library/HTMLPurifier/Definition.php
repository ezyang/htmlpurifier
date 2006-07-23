<?php

require_once 'HTMLPurifier/AttrDef.php';
require_once 'HTMLPurifier/ChildDef.php';
require_once 'HTMLPurifier/Generator.php';
require_once 'HTMLPurifier/Token.php';

class HTMLPurifier_Definition
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
    
    function HTMLPurifier_Definition() {
        $this->generator = new HTMLPurifier_Generator();
    }
    
    function loadData() {
        // emulates the structure of the DTD
        
        // entities: prefixed with e_ and _ replaces .
        // we don't use an array because that complicates interpolation
        // strings are used instead of arrays because if you use arrays,
        // you have to do some hideous manipulation with array_merge()
        
        // these are condensed, remember, with bad stuff taken out
        
        // transforms: font, menu, dir, center
        
        // DON'T MONKEY AROUND THIS unless you know what you are doing
        // and also know the assumptions the code makes about what this
        // contains for optimization purposes (see fixNesting)
        
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
        $e_Inline = new HTMLPurifier_ChildDef_Optional("#PCDATA | $e_inline".
          " | $e_misc_inline");
        $e_heading = 'h1|h2|h3|h4|h5|h6';
        $e_lists = 'ul | ol | dl';
        $e_blocktext = 'pre | hr | blockquote | address';
        $e_block = "p | $e_heading | div | $e_lists | $e_blocktext | table";
        $e_Flow = new HTMLPurifier_ChildDef_Optional("#PCDATA | $e_block".
          " | $e_inline | $e_misc");
        $e_a_content = new HTMLPurifier_ChildDef_Optional("#PCDATA | $e_special".
          " | $e_fontstyle | $e_phrase | $e_inline_forms | $e_misc_inline");
        $e_pre_content = new HTMLPurifier_ChildDef_Optional("#PCDATA | a".
          " | $e_special_basic | $e_fontstyle_basic | $e_phrase_basic".
          " | $e_inline_forms | $e_misc_inline");
        $e_form_content = new HTMLPurifier_ChildDef_Optional(''); //unused
        $e_form_button_content = new HTMLPurifier_ChildDef_Optional(''); // unused
        
        $this->info['ins'] =
        $this->info['del'] = 
        $this->info['blockquote'] =
        $this->info['dd']  =
        $this->info['li']  =
        $this->info['div'] = new HTMLPurifier_ElementDef($e_Flow);
        
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
        $this->info['h6']   = new HTMLPurifier_ElementDef($e_Inline);
        
        $this->info['ol']   =
        $this->info['ul']   =
          new HTMLPurifier_ElementDef(
            new HTMLPurifier_ChildDef_Required('li')
          );
        
        $this->info['dl']   =
          new HTMLPurifier_ElementDef(
            new HTMLPurifier_ChildDef_Required('dt|dd')
          );
        $this->info['address'] =
          new HTMLPurifier_ElementDef(
            new HTMLPurifier_ChildDef_Optional("#PCDATA | p | $e_inline".
              " | $e_misc_inline")
          );
        
        $this->info['img']  =
        $this->info['br']   =
        $this->info['hr']   = new HTMLPurifier_ElementDef(new HTMLPurifier_ChildDef_Empty());
        
        $this->info['pre']  = new HTMLPurifier_ElementDef($e_pre_content);
        
        $this->info['a']    = new HTMLPurifier_ElementDef($e_a_content);
        
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
            if (!empty( $token->is_tag )) {
                if (!isset($this->info[$token->name])) {
                    // invalid tag, generate HTML and insert in
                    $token = new HTMLPurifier_Token_Text(
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
            if (empty( $token->is_tag )) {
                $result[] = $token;
                continue;
            }
            $info = $this->info[$token->name]; // assumption but valid
            
            // test if it claims to be a start tag but is empty
            if ($info->child_def->type == 'empty' &&
                $token->type == 'start' ) {
                
                $result[] = new HTMLPurifier_Token_Empty($token->name,
                                                         $token->attributes);
                continue;
            }
            
            // test if it claims to be empty but really is a start tag
            if ($info->child_def->type != 'empty' &&
                $token->type == 'empty' ) {
                
                $result[] = new HTMLPurifier_Token_Start($token->name,
                                                         $token->attributes);
                $result[] = new HTMLPurifier_Token_End($token->name);
                
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
                        $result[] = new HTMLPurifier_Token_End('p');
                        $result[] = $token;
                        $current_nesting[] = $token;
                        continue;
                    }
                    
                    // check if we're closing a LI tag
                    if ($current_parent->name == 'li' &&
                        $token->name == 'li'
                        ) {
                        $result[] = new HTMLPurifier_Token_End('li');
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
                $result[] = new HTMLPurifier_Token_Text(
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
                $result[] = new HTMLPurifier_Token_Text(
                    $this->generator->generateFromToken($token)
                );
                continue;
            }
            
            // okay, we found it, close all the skipped tags
            // note that skipped tags contains the element we need closed
            $size = count($skipped_tags);
            for ($i = $size - 1; $i >= 0; $i--) {
                $result[] = new HTMLPurifier_Token_End($skipped_tags[$i]->name);
            }
            
            // done!
            
        }
        
        // we're at the end now, fix all still unclosed tags
        
        if (!empty($current_nesting)) {
            $size = count($current_nesting);
            for ($i = $size - 1; $i >= 0; $i--) {
                $result[] =
                    new HTMLPurifier_Token_End($current_nesting[$i]->name);
            }
        }
        
        return $result;
    }
    
    function fixNesting($tokens) {
        if (empty($this->info)) $this->loadData();
        
        // insert implicit "parent" node, will be removed at end
        array_unshift($tokens, new HTMLPurifier_Token_Start('div'));
        $tokens[] = new HTMLPurifier_Token_End('div');
        
        for ($i = 0, $size = count($tokens) ; $i < $size; ) {
            
            $child_tokens = array();
            
            // scroll to the end of this node, and report number
            for ($j = $i, $depth = 0; ; $j++) {
                if ($tokens[$j]->type == 'start') {
                    $depth++;
                    // skip token assignment on first iteration
                    if ($depth == 1) continue;
                } elseif ($tokens[$j]->type == 'end') {
                    $depth--;
                    // skip token assignment on last iteration
                    if ($depth == 0) break;
                }
                $child_tokens[] = $tokens[$j];
            }
            
            // $i is index of start token
            // $j is index of end token
            
            // have DTD child def validate children
            $element_def = $this->info[$tokens[$i]->name];
            $result = $element_def->child_def->validateChildren($child_tokens);
            
            // process result
            if ($result === true) {
                
                // leave the nodes as is
                
            } elseif($result === false) {
                
                // WARNING WARNING WARNING!!!
                // While for the original DTD, there will never be
                // cascading removal, more complex ones may have such
                // a problem.
                
                // If you modify the info array such that an element
                // that requires children may contain a child that requires
                // children, you need to also scroll back and re-check that
                // elements parent node
                
                $length = $j - $i + 1;
                
                // remove entire node
                array_splice($tokens, $i, $length);
                
                // change size
                $size -= $length;
                
                // ensure that we scroll to the next node
                $i--;
                
            } else {
                
                $length = $j - $i - 1;
                
                // replace node with $result
                array_splice($tokens, $i + 1, $length, $result);
                
                // change size
                $size -= $length;
                $size += count($result);
                
            }
            
            // scroll to next node
            $i++;
            while ($i < $size and $tokens[$i]->type != 'start') $i++;
            
        }
        
        // remove implicit divs
        array_shift($tokens);
        array_pop($tokens);
        
        return $tokens;
        
    }
    
    function validateAttributes($tokens) {
        if (empty($this->info)) $this->loadData();
        
    }
    
}

class HTMLPurifier_ElementDef
{
    
    var $child_def;
    var $attr_def = array();
    
    function HTMLPurifier_ElementDef($child_def, $attr_def = array()) {
        $this->child_def = $child_def;
        $this->attr_def  = $attr_def;
    }
    
}

?>