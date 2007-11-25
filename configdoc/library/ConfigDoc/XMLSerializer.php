<?php

/**
 * The XMLSerializer hierarchy of classes consist of classes that take
 * objects and serialize them into XML, specifically DOM, form; this
 * super-class contains convenience functions for those classes.
 */
class ConfigDoc_XMLSerializer
{
    
    /**
     * Appends a div containing HTML into a node
     * @param $document Base document node belongs to
     * @param $node Node to append to
     * @param $html HTML to place inside div to append
     * @todo Place this directly in DOMNode, using registerNodeClass to
     *       override.
     */
    protected function appendHTMLDiv($document, $node, $html) {
        $purifier = HTMLPurifier::getInstance();
        $html = $purifier->purify($html);
        $dom_html = $document->createDocumentFragment();
        $dom_html->appendXML($html);
        $dom_div = $document->createElement('div');
        $dom_div->setAttribute('xmlns', 'http://www.w3.org/1999/xhtml');
        $dom_div->appendChild($dom_html);
        $node->appendChild($dom_div);
    }
    
}

