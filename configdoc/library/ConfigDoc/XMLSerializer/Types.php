<?php

require_once 'ConfigDoc/XMLSerializer.php';

class ConfigDoc_XMLSerializer_Types extends ConfigDoc_XMLSerializer
{
    
    /**
     * Serializes the types in a schema into DOM form
     * @param $schema HTMLPurifier_ConfigSchema owner of types to serialize
     */
    public function serialize($schema) {
        $types_document = new DOMDocument('1.0', 'UTF-8');
        $types_root = $types_document->createElement('types');
        $types_document->appendChild($types_root);
        $types_document->formatOutput = true;
        foreach ($schema->types as $name => $expanded_name) {
            $types_type = $types_document->createElement('type', $expanded_name);
            $types_type->setAttribute('id', $name);
            $types_root->appendChild($types_type);
        }
        return $types_document;
    }
    
}

