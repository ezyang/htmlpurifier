<?php

class HTMLPurifierTest extends HTMLPurifier_Harness
{
    protected $purifier;

    function testNull() {
        $this->assertPurification("Null byte\0", "Null byte");
    }

    function test_purifyArray() {

        $this->assertIdentical(
            $this->purifier->purifyArray(
                array('Good', '<b>Sketchy', 'foo' => '<script>bad</script>')
            ),
            array('Good', '<b>Sketchy</b>', 'foo' => '')
        );

        $this->assertIsA($this->purifier->context, 'array');

    }

    function testGetInstance() {
        $purifier  = HTMLPurifier::getInstance();
        $purifier2 = HTMLPurifier::getInstance();
        $this->assertReference($purifier, $purifier2);
    }

    function testMakeAbsolute() {
        $this->config->set('URI.Base', 'http://example.com/bar/baz.php');
        $this->config->set('URI.MakeAbsolute', true);
        $this->assertPurification(
            '<a href="foo.txt">Foobar</a>',
            '<a href="http://example.com/bar/foo.txt">Foobar</a>'
        );
    }

    function testDisableResources() {
        $this->config->set('URI.DisableResources', true);
        $this->assertPurification('<img src="foo.jpg" />', '');
    }

    function test_addFilter_deprecated() {
        $this->expectError('HTMLPurifier->addFilter() is deprecated, use configuration directives in the Filter namespace or Filter.Custom');
        generate_mock_once('HTMLPurifier_Filter');
        $this->purifier->addFilter($mock = new HTMLPurifier_FilterMock());
        $mock->expectOnce('preFilter');
        $mock->expectOnce('postFilter');
        $this->purifier->purify('foo');
    }

    function test_hostwhitelist(){
        $this->config->set('URI.HostWhitelist',array('www.taobao.com','img01.daily.taobaocdn.net'));
        $this->config->set('AutoFormat.HostWhitelist', true);
        $this->assertPurification('<img src="foo.jpg" />', '<img alt="foo.jpg" />');

        $this->assertPurification('<img src="http://www.taobao.com/foo.jpg" />', '<img src="http://www.taobao.com/foo.jpg" alt="foo.jpg" />');

        $this->assertPurification('<a href="http://www.taobao.com/foo.jpg">test</a>', '<a href="http://www.taobao.com/foo.jpg">test</a>');

        $this->assertPurification('<a href="http://www.sina.com/foo.jpg">test</a>', '<a>test</a>');

    }

    function test_flashhostwhitelist(){
        $this->config->set('HTML.SafeEmbed', true);
        $this->config->set('HTML.SafeObject', true);
        $this->config->set('Output.FlashCompat', true);
        $this->config->set('HTML.FlashAllowFullScreen', true);//允许全屏
        $this->config->set('URI.FlashHostWhitelist',array('www.taobao.com','img01.daily.taobaocdn.net'));
        $this->config->set('AutoFormat.FlashHostWhitelist', true);

        $content = "<object><param name='video' value='http://www.a.com' /></object>";

        $this->assertPurification($content, '<object type="application/x-shockwave-flash"><param name="allowScriptAccess" value="never" /><param name="allowNetworking" value="internal" /></object>');

        $content = "<object><param name='movie' value='http://www.taobao.com/a.swf' /></object>";

        $this->assertPurification($content, '<object data="http://www.taobao.com/a.swf" type="application/x-shockwave-flash"><param name="allowScriptAccess" value="never" /><param name="allowNetworking" value="internal" /><param name="movie" value="http://www.taobao.com/a.swf" /></object>');

        //param name wrong
        $content = "<object><param name='video' value='http://www.taobao.com/a.swf' /></object>";

        $this->assertpurification($content, '<object type="application/x-shockwave-flash"><param name="allowScriptAccess" value="never" /><param name="allowNetworking" value="internal" /></object>');

        $content = "<object data='http://www.a.com/a.swf'><param name='movie' value='http://www.taobao.com/a.swf' /></object>";

        $this->assertPurification($content, '<object data="http://www.taobao.com/a.swf" type="application/x-shockwave-flash"><param name="allowScriptAccess" value="never" /><param name="allowNetworking" value="internal" /><param name="movie" value="http://www.taobao.com/a.swf" /></object>');

        $content = "<object data='http://www.taobao.com/a.swf'><param name='movie' value='http://www.b.com/a.swf' /></object>";

        $this->assertPurification($content, '<object data="http://www.taobao.com/a.swf" type="application/x-shockwave-flash"><param name="allowScriptAccess" value="never" /><param name="allowNetworking" value="internal" /><param name="movie" value="" /></object>');

        $content = "<object data='http://www.taobao.com/a.swf'></object>";

        $this->assertPurification($content, '<object data="http://www.taobao.com/a.swf" type="application/x-shockwave-flash"><param name="allowScriptAccess" value="never" /><param name="allowNetworking" value="internal" /></object>');

    }


}

// vim: et sw=4 sts=4
