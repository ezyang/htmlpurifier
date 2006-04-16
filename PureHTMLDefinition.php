<?php

class PureHTMLDefinition
{
    
    var $generator;
    var $info = array();
    
    function PureHTMLDefinition() {
        $this->generator = new HTML_Generator();
    }
    
    function loadData() {
        // emulates the structure of the DTD
        
        // array(
        //   array of allowed child elements,
        //   array of rejected child elements
        //   indication about how many child elements are needed
        // )
        
        $entity['special.extra'] = array('img');
        $entity['special.basic'] = array('br','bdo','span');
        $entity['special'] = array_merge($entity['special.basic'],
          $entity['special.extra']);
        
        $entity['fontstyle.extra'] = array('big','small');
        $entity['fontstyle.basic'] = array('tt','i','b','u','s','strike');
        $entity['fontstyle'] = array_merge($entity['fontstyle.extra'],
          $entity['fontstyle.basic']);
        
        $entity['phrase.extra'] = array('sub','sup');
        $entity['phrase.basic'] = array('em','strong','dfn','code','samp','kbd',
          'var','cite','abbr','acronym','q');
        $entity['phrase'] = array_merge($entity['phrase.extra'],
          $entity['phrase.basic']);
        
        $entity['misc.inline'] = array('ins','del');
        $entity['misc'] = $entity['misc.inline'];
        
        $entity['inline'] = array_merge(array('a'), $entity['special'],
          $entity['fontstyle'], $entity['phrase']);
        
        $entity['heading'] = array('h1','h2','h3','h4','h5','h6');
        $entity['lists'] = array('ul','ol', 'dl');
        $entity['blocktext'] = array('pre','hr','blockquote','address');
        
        $entity['block'] = array_merge(array('p','div','table'),
          $entity['heading'],$entity['lists'], $entity['blocktext']);
        
        $entity['Inline'] = array_merge(array('#PCDATA'),$entity['special'],
          $entity['misc.inline']);
        $entity['Flow'] = array_merge(array('#PCDATA'), $entity['block'],
          $entity['inline'], $entity['misc']);
        $entity['a.content'] = array_merge(array('#PCDATA'), $entity['special'],
          $entity['fontstyle'], $entity['phrase'], $entity['misc.inline']);
        
        $entity['pre.content'] = array_merge(array('#PCDATA', 'a'),
          $entity['special.basic'], $entity['fontstyle.basic'],
          $entity['phrase.basic'], $entity['misc.inline']);
        
        $this->info['ins'] =
        $this->info['del'] = 
        $this->info['blockquote'] =
        $this->info['dd']  =
        $this->info['div'] = array($entity['Flow']);
        
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
        $this->info['h6']   = array($entity['Inline']);
        
        $this->info['ol']   =
        $this->info['ul']   = array(array('li'),array(),'+');
        // the plus requires at least one child. I don't know what the
        // empty array is for though
        
        $this->info['dl']   = array(array('dt','dd'));
        $this->info['address'] =
          array(
            array_merge(
              array('#PCDATA', 'p'),
              $entity['inline'],
              $entity['misc.inline']));
        
        $this->info['img']  =
        $this->info['br']   =
        $this->info['hr']   = 'EMPTY';
        
        $this->info['pre']  = array($entity['pre.content']);
        
        $this->info['a']    = array($entity['a.content']);
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
                    $token = new MF_Text($this->generator->generateFromToken($token));
                }
            } elseif (is_a($token, 'MF_Comment')) {
                // strip comments
                continue;
            } elseif (is_a($token, 'MF_Text')) {
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
            if (!is_subclass_of($token, 'MF_Tag')) $result[] = $token;
            // test if it claims to be a start tag but is empty
        }
    }
    
    function fixNesting($tokens) {
        if (empty($this->info)) $this->loadData();
        
    }
    
    function validateAttributes($tokens) {
        if (empty($this->info)) $this->loadData();
        
    }
    
}

class HTMLDTD_Element
{
    
    var $child_def;
    var $attr_def = array();
    
    
}

class HTMLDTD_ChildDef {
    var $dtd_regex;
    function HTMLDTD_ChildDef($dtd_regex) {
        $this->dtd_regex = $dtd_regex;
    }
    function validateChildren($tokens_of_children) {}
}
class HTMLDTD_ChildDef_Simple extends HTMLDTD_ChildDef {
    var $elements = array();
    function HTMLDTD_ChildDef_Simple($elements) {
        $this->elements = $elements;
    }
}
class HTMLDTD_ChildDef_Required extends HTMLDTD_ChildDef_Simple {
    function validateChildren($tokens_of_children) {
    
    }
}
class HTMLDTD_ChildDef_Optional extends HTMLDTD_ChildDef_Simple {
    function validateChildren($tokens_of_children) {
        
    }
}

class HTMLDTD_AttrDef {
    var $def;
    function HTMLDTD_AttrDef($def) {
        $this->def = $def;
    }
}

?>