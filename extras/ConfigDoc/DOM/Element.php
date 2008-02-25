<?php

class ConfigDoc_DOM_Element extends DOMElement
{
    
    /**
     * Appends an HTML div to this node
     */
    public function appendHTMLDiv($html) {
        $this->appendChild($this->generateHTMLDiv($html));
    }
    
    /**
     * Generates an HTML div that can contain arbitrary markup
     */
    protected function generateHTMLDiv($html) {
        $purifier = HTMLPurifier::getInstance();
        $html = $purifier->purify($html);
        $dom_html = $this->ownerDocument->createDocumentFragment();
        $dom_html->appendXML($html);
        $dom_div = $this->ownerDocument->createElement('div');
        $dom_div->setAttribute('xmlns', 'http://www.w3.org/1999/xhtml');
        $dom_div->appendChild($dom_html);
        return $dom_div;
    }
    
}
