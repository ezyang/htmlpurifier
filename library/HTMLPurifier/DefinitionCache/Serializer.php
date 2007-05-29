<?php

require_once 'HTMLPurifier/DefinitionCache.php';

class HTMLPurifier_DefinitionCache_Serializer extends
      HTMLPurifier_DefinitionCache
{
    
    function add($def, $config) {
        if (!$this->checkDefType($def)) return;
        $file = $this->generateFilePath($config);
        if (file_exists($file)) return false;
        return $this->_write($file, serialize($def));
    }
    
    function set($def, $config) {
        if (!$this->checkDefType($def)) return;
        $file = $this->generateFilePath($config);
        return $this->_write($file, serialize($def));
    }
    
    function replace($def, $config) {
        if (!$this->checkDefType($def)) return;
        $file = $this->generateFilePath($config);
        if (!file_exists($file)) return false;
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
    
    function flush() {
        $dir = $this->generateDirectoryPath();
        $dh  = opendir($dir);
        while (false !== ($filename = readdir($dh))) {
            if (empty($filename)) continue;
            if ($filename[0] === '.') continue;
            unlink($dir . '/' . $filename);
        }
    }
    
    function cleanup($config) {
        $dir = $this->generateDirectoryPath();
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
        return $this->generateDirectoryPath() . '/' . $key . '.ser';
    }
    
    /**
     * Generates the path to the directory contain this cache's serial files
     * @note No trailing slash
     */
    function generateDirectoryPath() {
        return dirname(__FILE__) . '/Serializer/' . $this->type;
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
    
}

?>