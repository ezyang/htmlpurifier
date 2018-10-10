<?php

/**
 * Positive tests (expected to pass) have been extracted from Mozilla's torture
 * tests for MathML. See:
 * https://developer.mozilla.org/en-US/docs/Mozilla/MathML_Project/MathML_Torture_Test
 * Comments have been removed.
 * 
 * Negative tests (those expected to fail) have been extracted from
 * W3C's MathML Test Suite. See:
 * http://www.w3.org/Math/testsuite/build/mathml3tests.zip -> ErrorHandling folder
 */
class HTMLPurifier_HTMLModule_MathBasicTest extends HTMLPurifier_HTMLModuleHarness
{

    public function setup() {

        parent::setup();
        $this->config->set('HTML.Math', true);

        // We load each snippet and its purified version each into a
        // separate XML document. This normalizes some self-closing
        // tags which can be either <a></a> or <a/> or <a /> into a
        // common format to compare the strings properly.
        $this->pre = new DOMDocument();
        $this->post = new DOMDocument();

    }

    // Correctly formed MathML trees
    public function testGood()
    {

        foreach (glob('MathML/basic/good/*.mml') as $filename) {

            $snippet = file_get_contents($filename);

            $this->pre->loadXML($snippet);
            $this->pre->normalizeDocument();

            $this->post->loadXML($this->purifier->purify($snippet, $this->config));
            $this->post->normalizeDocument();

            $this->assertIdentical($this->pre->saveXML(), $this->post->saveXML());

        }

    }

    // Incorrectly formed MathML trees
    public function testBad() {

        foreach (glob('MathML/basic/bad/*.mml') as $filename) {

            $snippet = file_get_contents($filename);

            $this->pre->loadXML($snippet);
            $this->pre->normalizeDocument();

            $this->post->loadXML($this->purifier->purify($snippet, $this->config));
            $this->post->normalizeDocument();

            $this->assertFalse($this->pre->saveXML() == $this->post->saveXML());

        }

    }

    // Incorrectly formed MathML trees that yield an error
    public function testError() {

        $snippet = '' .
'<math xmlns="http://www.w3.org/1998/Math/MathML">
  <math>
    <msup>
      <mn>5</mn> 
      <mn>2</mn> 
    </msup> 
  </math> 
</math>';

        $this->expectError();

        $this->pre->loadXML($snippet);
        $this->pre->normalizeDocument();

        $this->post->loadXML($this->purifier->purify($snippet, $this->config));
        $this->post->normalizeDocument();

        $this->assertFalse($this->pre->saveXML() == $this->post->saveXML());

    }



}

// vim: et sw=4 sts=4
