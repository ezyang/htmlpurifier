<?php

require_once 'HTMLPurifier/Lexer/DirectLex.php';

class HTMLPurifier_LexerTest extends HTMLPurifier_Harness
{
    
    var $Lexer;
    var $DirectLex, $PEARSax3, $DOMLex;
    var $_entity_lookup;
    var $_has_pear = false;
    var $_has_dom  = false;
    
    function setUp() {
        $this->Lexer     = new HTMLPurifier_Lexer();
        
        $this->DirectLex = new HTMLPurifier_Lexer_DirectLex();
        
        if ( $GLOBALS['HTMLPurifierTest']['PEAR'] && 
             ((error_reporting() & E_STRICT) != E_STRICT)
        ) {
            $this->_has_pear = true;
            require_once 'HTMLPurifier/Lexer/PEARSax3.php';
            $this->PEARSax3  = new HTMLPurifier_Lexer_PEARSax3();
        }
        
        $this->_has_dom = version_compare(PHP_VERSION, '5', '>=');
        if ($this->_has_dom) {
            require_once 'HTMLPurifier/Lexer/DOMLex.php';
            $this->DOMLex    = new HTMLPurifier_Lexer_DOMLex();
        }
        
        $this->_entity_lookup = HTMLPurifier_EntityLookup::instance();
        
    }
    
    function test_create() {
        $config = HTMLPurifier_Config::create(array('Core.MaintainLineNumbers' => true));
        $lexer = HTMLPurifier_Lexer::create($config);
        $this->assertIsA($lexer, 'HTMLPurifier_Lexer_DirectLex');
    }
    
    function assertExtractBody($text, $extract = true) {
        $result = $this->Lexer->extractBody($text);
        if ($extract === true) $extract = $text;
        $this->assertIdentical($extract, $result);
    }
    
    function test_parseData() {
        $HP =& $this->Lexer;
        
        $this->assertIdentical('asdf', $HP->parseData('asdf'));
        $this->assertIdentical('&', $HP->parseData('&amp;'));
        $this->assertIdentical('"', $HP->parseData('&quot;'));
        $this->assertIdentical("'", $HP->parseData('&#039;'));
        $this->assertIdentical("'", $HP->parseData('&#39;'));
        $this->assertIdentical('&&&', $HP->parseData('&amp;&amp;&amp;'));
        $this->assertIdentical('&&', $HP->parseData('&amp;&')); // [INVALID]
        $this->assertIdentical('Procter & Gamble',
                $HP->parseData('Procter & Gamble')); // [INVALID]
        
        // This is not special, thus not converted. Test of fault tolerance,
        // realistically speaking, this should never happen
        $this->assertIdentical('&#x2D;', $HP->parseData('&#x2D;'));
    }
    
    
    function test_extractBody() {
        $this->assertExtractBody('<b>Bold</b>');
        $this->assertExtractBody('<html><body><b>Bold</b></body></html>', '<b>Bold</b>');
        $this->assertExtractBody('<HTML><BODY><B>Bold</B></BODY></HTML>', '<B>Bold</B>');
        $this->assertExtractBody(
'<?xml version="1.0"
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN"
    "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
   <head>
      <title>xyz</title>
   </head>
   <body>
      <form method="post" action="whatever1">
         <div>
            <input type="text" name="username" />
            <input type="text" name="password" />
            <input type="submit" />
         </div>
      </form>
   </body>
</html>',
    '
      <form method="post" action="whatever1">
         <div>
            <input type="text" name="username" />
            <input type="text" name="password" />
            <input type="submit" />
         </div>
      </form>
   ');
        $this->assertExtractBody('<html><body bgcolor="#F00"><b>Bold</b></body></html>', '<b>Bold</b>');
        $this->assertExtractBody('<body>asdf'); // not closed, don't accept
        
    }
    
    function test_tokenizeHTML() {
        
        $input = array();
        $expect = array();
        $sax_expect = array();
        $config = array();
        
        $input[0] = '';
        $expect[0] = array();
        
        $input[1] = 'This is regular text.';
        $expect[1] = array(
            new HTMLPurifier_Token_Text('This is regular text.')
            );
        
        $input[2] = 'This is <b>bold</b> text';
        $expect[2] = array(
            new HTMLPurifier_Token_Text('This is ')
           ,new HTMLPurifier_Token_Start('b', array())
           ,new HTMLPurifier_Token_Text('bold')
           ,new HTMLPurifier_Token_End('b')
           ,new HTMLPurifier_Token_Text(' text')
            );
        
        $input[3] = '<DIV>Totally rad dude. <b>asdf</b></div>';
        $expect[3] = array(
            new HTMLPurifier_Token_Start('DIV', array())
           ,new HTMLPurifier_Token_Text('Totally rad dude. ')
           ,new HTMLPurifier_Token_Start('b', array())
           ,new HTMLPurifier_Token_Text('asdf')
           ,new HTMLPurifier_Token_End('b')
           ,new HTMLPurifier_Token_End('div')
            );
        
        // [XML-INVALID]
        $input[4] = '<asdf></asdf><d></d><poOloka><poolasdf><ds></asdf></ASDF>';
        $expect[4] = array(
            new HTMLPurifier_Token_Start('asdf')
           ,new HTMLPurifier_Token_End('asdf')
           ,new HTMLPurifier_Token_Start('d')
           ,new HTMLPurifier_Token_End('d')
           ,new HTMLPurifier_Token_Start('poOloka')
           ,new HTMLPurifier_Token_Start('poolasdf')
           ,new HTMLPurifier_Token_Start('ds')
           ,new HTMLPurifier_Token_End('asdf')
           ,new HTMLPurifier_Token_End('ASDF')
            );
        // DOM is different because it condenses empty tags into REAL empty ones
        // as well as makes it well-formed
        $dom_expect[4] = array(
            new HTMLPurifier_Token_Empty('asdf')
           ,new HTMLPurifier_Token_Empty('d')
           ,new HTMLPurifier_Token_Start('pooloka')
           ,new HTMLPurifier_Token_Start('poolasdf')
           ,new HTMLPurifier_Token_Empty('ds')
           ,new HTMLPurifier_Token_End('poolasdf')
           ,new HTMLPurifier_Token_End('pooloka')
            );
        
        $input[5] = '<a'."\t".'href="foobar.php"'."\n".'title="foo!">Link to <b id="asdf">foobar</b></a>';
        $expect[5] = array(
            new HTMLPurifier_Token_Start('a',array('href'=>'foobar.php','title'=>'foo!'))
           ,new HTMLPurifier_Token_Text('Link to ')
           ,new HTMLPurifier_Token_Start('b',array('id'=>'asdf'))
           ,new HTMLPurifier_Token_Text('foobar')
           ,new HTMLPurifier_Token_End('b')
           ,new HTMLPurifier_Token_End('a')
            );
        
        $input[6] = '<br />';
        $expect[6] = array(
            new HTMLPurifier_Token_Empty('br')
            );
        
        // [SGML-INVALID] [RECOVERABLE]
        $input[7] = '<!-- Comment --> <!-- not so well formed --->';
        $expect[7] = array(
            new HTMLPurifier_Token_Comment(' Comment ')
           ,new HTMLPurifier_Token_Text(' ')
           ,new HTMLPurifier_Token_Comment(' not so well formed -')
            );
        $sax_expect[7] = false; // we need to figure out proper comment output
        
        // [SGML-INVALID]
        $input[8] = '<a href=""';
        $expect[8] = array(
            new HTMLPurifier_Token_Text('<a href=""')
            );
        // SAX parses it into a tag
        $sax_expect[8] = array(
            new HTMLPurifier_Token_Start('a', array('href'=>''))
            ); 
        // DOM parses it into an empty tag
        $dom_expect[8] = array(
            new HTMLPurifier_Token_Empty('a', array('href'=>''))
            ); 
        
        $input[9] = '&lt;b&gt;';
        $expect[9] = array(
            new HTMLPurifier_Token_Text('<b>')
            );
        $sax_expect[9] = array(
            new HTMLPurifier_Token_Text('<')
           ,new HTMLPurifier_Token_Text('b')
           ,new HTMLPurifier_Token_Text('>')
            );
        // note that SAX can clump text nodes together. We won't be
        // too picky though
        
        // [SGML-INVALID]
        $input[10] = '<a "=>';
        // We barf on this, aim for no attributes
        $expect[10] = array(
            new HTMLPurifier_Token_Start('a', array('"' => ''))
            );
        // DOM correctly has no attributes, but also closes the tag
        $dom_expect[10] = array(
            new HTMLPurifier_Token_Empty('a')
            );
        // SAX barfs on this
        $sax_expect[10] = array(
            new HTMLPurifier_Token_Start('a', array('"' => ''))
            );
        
        // [INVALID] [RECOVERABLE]
        $input[11] = '"';
        $expect[11] = array( new HTMLPurifier_Token_Text('"') );
        
        // compare with this valid one:
        $input[12] = '&quot;';
        $expect[12] = array( new HTMLPurifier_Token_Text('"') );
        $sax_expect[12] = false; // choked!
        
        // CDATA sections!
        $input[13] = '<![CDATA[You <b>can&#39;t</b> get me!]]>';
        $expect[13] = array( new HTMLPurifier_Token_Text(
            'You <b>can&#39;t</b> get me!' // raw
            ) );
        $sax_expect[13] = array( // SAX has a seperate call for each entity
                new HTMLPurifier_Token_Text('You '),
                new HTMLPurifier_Token_Text('<'),
                new HTMLPurifier_Token_Text('b'),
                new HTMLPurifier_Token_Text('>'),
                new HTMLPurifier_Token_Text('can'),
                new HTMLPurifier_Token_Text('&'),
                new HTMLPurifier_Token_Text('#39;t'),
                new HTMLPurifier_Token_Text('<'),
                new HTMLPurifier_Token_Text('/b'),
                new HTMLPurifier_Token_Text('>'),
                new HTMLPurifier_Token_Text(' get me!')
            );
        
        $char_theta = $this->_entity_lookup->table['theta'];
        $char_rarr  = $this->_entity_lookup->table['rarr'];
        
        // test entity replacement
        $input[14] = '&theta;';
        $expect[14] = array( new HTMLPurifier_Token_Text($char_theta) );
        
        // test that entities aren't replaced in CDATA sections
        $input[15] = '&theta; <![CDATA[&rarr;]]>';
        $expect[15] = array( new HTMLPurifier_Token_Text($char_theta . ' &rarr;') );
        $sax_expect[15] = array(
                new HTMLPurifier_Token_Text($char_theta . ' '),
                new HTMLPurifier_Token_Text('&'),
                new HTMLPurifier_Token_Text('rarr;')
            );
        
        // test entity resolution in attributes
        $input[16] = '<a href="index.php?title=foo&amp;id=bar">Link</a>';
        $expect[16] = array(
                new HTMLPurifier_Token_Start('a',array('href' => 'index.php?title=foo&id=bar'))
               ,new HTMLPurifier_Token_Text('Link')
               ,new HTMLPurifier_Token_End('a')
            );
        
        // test that UTF-8 is preserved
        $char_hearts = $this->_entity_lookup->table['hearts'];
        $input[17] = $char_hearts;
        $expect[17] = array( new HTMLPurifier_Token_Text($char_hearts) );
        
        // test weird characters in attributes
        $input[18] = '<br test="x &lt; 6" />';
        $expect[18] = array( new HTMLPurifier_Token_Empty('br', array('test' => 'x < 6')) );
        
        // test emoticon protection
        $input[19] = '<b>Whoa! <3 That\'s not good >.></b>';
        $expect[19] = array(
            new HTMLPurifier_Token_Start('b'),
            new HTMLPurifier_Token_Text('Whoa! '),
            new HTMLPurifier_Token_Text('<3 That\'s not good >'),
            new HTMLPurifier_Token_Text('.>'),
            new HTMLPurifier_Token_End('b'),
        );
        $dom_expect[19] = array(
            new HTMLPurifier_Token_Start('b'),
            new HTMLPurifier_Token_Text('Whoa! <3 That\'s not good >.>'),
            new HTMLPurifier_Token_End('b'),
        );
        $sax_expect[19] = false; // SAX drops the < character
        $config[19] = HTMLPurifier_Config::create(array('Core.AggressivelyFixLt' => true));
        
        // test comment parsing with funky characters inside
        $input[20] = '<!-- This >< comment --><br />';
        $expect[20] = array(
            new HTMLPurifier_Token_Comment(' This >< comment '),
            new HTMLPurifier_Token_Empty('br')
        );
        $sax_expect[20] = false;
        $config[20] = HTMLPurifier_Config::create(array('Core.AggressivelyFixLt' => true));
        
        // test comment parsing of missing end
        $input[21] = '<!-- This >< comment';
        $expect[21] = array(
            new HTMLPurifier_Token_Comment(' This >< comment')
        );
        $sax_expect[21] = false;
        $dom_expect[21] = false;
        $config[21] = HTMLPurifier_Config::create(array('Core.AggressivelyFixLt' => true));
        
        // test CDATA tags
        $input[22] = '<script>alert("<foo>");</script>';
        $expect[22] = array(
            new HTMLPurifier_Token_Start('script')
           ,new HTMLPurifier_Token_Text('alert("<foo>");')
           ,new HTMLPurifier_Token_End('script')
        );
        $config[22] = HTMLPurifier_Config::create(array('HTML.Trusted' => true));
        $sax_expect[22] = false;
        
        // test escaping
        $input[23] = '<!-- This comment < &lt; & -->';
        $expect[23] = array(
            new HTMLPurifier_Token_Comment(' This comment < &lt; & ') );
        $sax_expect[23] = false; $config[23] =
        HTMLPurifier_Config::create(array('Core.AggressivelyFixLt' =>
        true));
        
        // more DirectLex edge-cases 
        $input[24] = '<a href="><>">';
        $expect[24] = array(
            new HTMLPurifier_Token_Start('a', array('href' => '')),
            new HTMLPurifier_Token_Text('<">')
        );
        $sax_expect[24] = false;
        $dom_expect[24] = array(
            new HTMLPurifier_Token_Empty('a', array('href' => '><>'))
        );
        
        $default_config = HTMLPurifier_Config::createDefault();
        $default_context = new HTMLPurifier_Context();
        foreach($input as $i => $discard) {
            if (!isset($config[$i])) $config[$i] = $default_config;
            
            $result = $this->DirectLex->tokenizeHTML($input[$i], $config[$i], $default_context);
            $this->assertIdentical($expect[$i], $result, 'DirectLexTest '.$i.': %s');
            paintIf($result, $expect[$i] != $result);
            
            if ($this->_has_pear) {
                // assert unless I say otherwise
                $sax_result = $this->PEARSax3->tokenizeHTML($input[$i], $config[$i], $default_context);
                if (!isset($sax_expect[$i])) {
                    // by default, assert with normal result
                    $this->assertIdentical($expect[$i], $sax_result, 'PEARSax3Test '.$i.': %s');
                    paintIf($sax_result, $expect[$i] != $sax_result);
                } elseif ($sax_expect[$i] === false) {
                    // assertions were turned off, optionally dump
                    // paintIf($sax_expect, $i == NUMBER);
                } else {
                    // match with a custom SAX result array
                    $this->assertIdentical($sax_expect[$i], $sax_result, 'PEARSax3Test (custom) '.$i.': %s');
                    paintIf($sax_result, $sax_expect[$i] != $sax_result);
                }
            }
            
            if ($this->_has_dom) {
                $dom_result = $this->DOMLex->tokenizeHTML($input[$i], $config[$i], $default_context);
                // same structure as SAX
                if (!isset($dom_expect[$i])) {
                    $this->assertIdentical($expect[$i], $dom_result, 'DOMLexTest '.$i.': %s');
                    paintIf($dom_result, $expect[$i] != $dom_result);
                } elseif ($dom_expect[$i] === false) {
                    // paintIf($dom_result, $i == NUMBER);
                } else {
                    $this->assertIdentical($dom_expect[$i], $dom_result, 'DOMLexTest (custom) '.$i.': %s');
                    paintIf($dom_result, $dom_expect[$i] != $dom_result);
                }
            }
            
        }
        
    }
    
}

