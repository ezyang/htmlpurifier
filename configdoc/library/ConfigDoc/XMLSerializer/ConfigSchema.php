<?php

require_once 'ConfigDoc/XMLSerializer.php';

class ConfigDoc_XMLSerializer_ConfigSchema extends ConfigDoc_XMLSerializer
{
    
    /**
     * Serializes a schema into DOM form
     * @todo Split into sub-serializers
     * @param $schema HTMLPurifier_ConfigSchema to serialize
     */
    public function serialize($schema) {
        $dom_document = new DOMDocument('1.0', 'UTF-8');
        $dom_root = $dom_document->createElement('configdoc');
        $dom_document->appendChild($dom_root);
        $dom_document->formatOutput = true;
        
        // add the name of the application
        $dom_root->appendChild($dom_document->createElement('title', 'HTML Purifier'));
        
        /*
        TODO for XML format:
        - create a definition (DTD or other) once interface stabilizes
        */
        
        foreach($schema->info as $namespace_name => $namespace_info) {
            
            $dom_namespace = $dom_document->createElement('namespace');
            $dom_root->appendChild($dom_namespace);
            
            $dom_namespace->setAttribute('id', $namespace_name);
            $dom_namespace->appendChild(
                $dom_document->createElement('name', $namespace_name)
            );
            $dom_namespace_description = $dom_document->createElement('description');
            $dom_namespace->appendChild($dom_namespace_description);
            $this->appendHTMLDiv($dom_document, $dom_namespace_description,
                $schema->info_namespace[$namespace_name]->description);
            
            foreach ($namespace_info as $name => $info) {
                
                if ($info->class == 'alias') continue;
                
                $dom_directive = $dom_document->createElement('directive');
                $dom_namespace->appendChild($dom_directive);
                
                $dom_directive->setAttribute('id', $namespace_name . '.' . $name);
                $dom_directive->appendChild(
                    $dom_document->createElement('name', $name)
                );
                
                $dom_aliases = $dom_document->createElement('aliases');
                $dom_directive->appendChild($dom_aliases);
                foreach ($info->directiveAliases as $alias) {
                    $dom_aliases->appendChild($dom_document->createElement('alias', $alias));
                }
                
                $dom_constraints = $dom_document->createElement('constraints');
                $dom_directive->appendChild($dom_constraints);
                
                $dom_type = $dom_document->createElement('type', $info->type);
                if ($info->allow_null) {
                    $dom_type->setAttribute('allow-null', 'yes');
                }
                $dom_constraints->appendChild($dom_type);
                
                if ($info->allowed !== true) {
                    $dom_allowed = $dom_document->createElement('allowed');
                    $dom_constraints->appendChild($dom_allowed);
                    foreach ($info->allowed as $allowed => $bool) {
                        $dom_allowed->appendChild(
                            $dom_document->createElement('value', $allowed)
                        );
                    }
                }
                
                $raw_default = $schema->defaults[$namespace_name][$name];
                if (is_bool($raw_default)) {
                    $default = $raw_default ? 'true' : 'false';
                } elseif (is_string($raw_default)) {
                    $default = "\"$raw_default\"";
                } elseif (is_null($raw_default)) {
                    $default = 'null';
                } else {
                    $default = print_r(
                            $schema->defaults[$namespace_name][$name], true
                        );
                }
                
                $dom_default = $dom_document->createElement('default', $default);
                
                // remove this once we get a DTD
                $dom_default->setAttribute('xml:space', 'preserve');
                
                $dom_constraints->appendChild($dom_default);
                
                $dom_descriptions = $dom_document->createElement('descriptions');
                $dom_directive->appendChild($dom_descriptions);
                
                foreach ($info->descriptions as $file => $file_descriptions) {
                    foreach ($file_descriptions as $line => $description) {
                        $dom_description = $dom_document->createElement('description');
                        // refuse to write $file if it's a full path
                        if (str_replace('\\', '/', realpath($file)) != $file) {
                            $dom_description->setAttribute('file', $file);
                            $dom_description->setAttribute('line', $line);
                        }
                        $this->appendHTMLDiv($dom_document, $dom_description, $description);
                        $dom_descriptions->appendChild($dom_description);
                    }
                }
                
            }
            
        }
        
        return $dom_document;
        
    }
    
}

