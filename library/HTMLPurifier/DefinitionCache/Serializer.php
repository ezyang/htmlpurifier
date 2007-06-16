<?php

require_once 'HTMLPurifier/DefinitionCache.php';

HTMLPurifier_ConfigSchema::define(
    'Cache', 'SerializerPath', null, 'string/null', '
<p>
    Absolute path with no trailing slash to store serialized definitions in.
    Default is within the
    HTML Purifier library inside DefinitionCache/Serializer. This
    path must be writable by the webserver. This directive has been
    available since 1.7.0.
</p>
');

class HTMLPurifier_DefinitionCache_Serializer extends
      HTMLPurifier_DefinitionCache
{
    
    function add($def, $config) {
        if (!$this->checkDefType($def)) return;
        $file = $this->generateFilePath($config);
        if (file_exists($file)) return false;
        $this->_prepareDir($config);
        return $this->_write($file, serialize($def));
    }
    
    function set($def, $config) {
        if (!$this->checkDefType($def)) return;
        $file = $this->generateFilePath($config);
        $this->_prepareDir($config);
        return $this->_write($file, serialize($def));
    }
    
    function replace($def, $config) {
        if (!$this->checkDefType($def)) return;
        $file = $this->generateFilePath($config);
        if (!file_exists($file)) return false;
        $this->_prepareDir($config);
        return $this->_write($file, serialize($def));
    }
    
    function get($config) {
        $file = $this->generateFilePath($config);
        if (!file_exists($file)) return false;
        return unserialize(file_get_contents($file));
    }
    
    function remove($config) {
        $file = $this->generateFilePath($config);
        if (!file_exists($file)) return false;
        return unlink($file);
    }
    
    function flush($config) {
        $dir = $this->generateDirectoryPath($config);
        $dh  = opendir($dir);
        while (false !== ($filename = readdir($dh))) {
            if (empty($filename)) continue;
            if ($filename[0] === '.') continue;
            unlink($dir . '/' . $filename);
        }
    }
    
    function cleanup($config) {
        $this->_prepareDir($config);
        $dir = $this->generateDirectoryPath($config);
        $dh  = opendir($dir);
        while (false !== ($filename = readdir($dh))) {
            if (empty($filename)) continue;
            if ($filename[0] === '.') continue;
            $key = substr($filename, 0, strlen($filename) - 4);
            if ($this->isOld($key, $config)) unlink($dir . '/' . $filename);
        }
    }
    
    /**
     * Generates the file path to the serial file corresponding to
     * the configuration and definition name
     */
    function generateFilePath($config) {
        $key = $this->generateKey($config);
        return $this->generateDirectoryPath($config) . '/' . $key . '.ser';
    }
    
    /**
     * Generates the path to the directory contain this cache's serial files
     * @note No trailing slash
     */
    function generateDirectoryPath($config) {
        $base = $config->get('Cache', 'SerializerPath');
        $base = is_null($base) ? dirname(__FILE__) . '/Serializer' : $base;
        return $base . '/' . $this->type;
    }
    
    /**
     * Convenience wrapper function for file_put_contents
     * @param $file File name to write to
     * @param $data Data to write into file
     * @return Number of bytes written if success, or false if failure.
     */
    function _write($file, $data) {
        static $file_put_contents;
        if ($file_put_contents === null) {
            $file_put_contents = function_exists('file_put_contents');
        }
        if ($file_put_contents) {
            return file_put_contents($file, $data);
        }
        $fh = fopen($file, 'w');
        if (!$fh) return false;
        $status = fwrite($fh, $data);
        fclose($fh);
        return $status;
    }
    
    /**
     * Prepares the directory that this type stores the serials in
     */
    function _prepareDir($config) {
        $directory = $this->generateDirectoryPath($config);
        if (!is_dir($directory)) {
            mkdir($directory);
        }
    }
    
}

?>