<?php

class HTMLPurifier_Lexer_DomLexTest extends HTMLPurifier_Harness
{

    protected $domLex;

    public function setUp()
    {
        $this->domLex = new HTMLPurifier_Lexer_DOMLex();
    }

    public function testCoreAggressivelyFixLtEmojis()
    {
        $context = new HTMLPurifier_Context();
        $config  = HTMLPurifier_Config::createDefault();
        $output = $this->domLex->tokenizeHTML('<b><3</b>', $config, $context);

        $this->assertIdentical($output, array(
            new HTMLPurifier_Token_Start('b'),
            new HTMLPurifier_Token_Text('<3'),
            new HTMLPurifier_Token_End('b')
        ));
    }

    public function testCoreAggressivelyFixLtComments()
    {
        $context = new HTMLPurifier_Context();
        $config  = HTMLPurifier_Config::createDefault();
        $output = $this->domLex->tokenizeHTML('<!-- Nested <!-- Not to be included --> comment -->', $config, $context);

        $this->assertIdentical($output, array(
            new HTMLPurifier_Token_Comment(' Nested <!-- Not to be included '),
            new HTMLPurifier_Token_Text(' comment -->')
        ));
    }

}

// vim: et sw=4 sts=4
