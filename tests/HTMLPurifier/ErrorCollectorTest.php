<?php

require_once 'HTMLPurifier/ErrorCollector.php';

class HTMLPurifier_ErrorCollectorTest extends UnitTestCase
{
    
    function test() {
        
        $tok1 = new HTMLPurifier_Token_Text('Token that caused error');
        $tok1->line = 23;
        $tok2 = new HTMLPurifier_Token_Start('a'); // also caused error
        $tok2->line = 3;
        $tok3 = new HTMLPurifier_Token_Text('Context before'); // before $tok2
        $tok3->line = 3;
        $tok4 = new HTMLPurifier_Token_Text('Context after'); // after $tok2
        $tok4->line = 3;
        
        $collector = new HTMLPurifier_ErrorCollector();
        $collector->send('Big fat error', $tok1);
        $collector->send('Another <error>', $tok2, array($tok3, true, $tok4));
        
        $result = array(
            0 => array('Big fat error', $tok1, array(true)),
            1 => array('Another <error>', $tok2, array($tok3, true, $tok4))
        );
        
        $this->assertIdentical($collector->getRaw(), $result);
        
        $formatted_result = array(
            0 => 'Another &lt;error&gt; at line 3 (<code>Context before<strong>&lt;a&gt;</strong>Context after</code>)',
            1 => 'Big fat error at line 23 (<code><strong>Token that caused error</strong></code>)'
        );
        
        $config = HTMLPurifier_Config::create(array('Core.MaintainLineNumbers' => true));
        $this->assertIdentical($collector->getHTMLFormatted($config), $formatted_result);
        
    }
    
}

?>