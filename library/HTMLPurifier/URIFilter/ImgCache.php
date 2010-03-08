<?php

class HTMLPurifier_URIFilter_ImgCache extends HTMLPurifier_URIFilter
{
    public $name = 'ImgCache';
    public $post = true;
    private $parser, $secretKey, $path, $maxWidth, $maxHeight;

    public function prepare($config) {
        $this->parser      = new HTMLPurifier_URIParser();
        $this->path        = $config->get('URI', 'ImgCachePath');
        $this->maxWidth    = $config->get('URI', 'ImgCacheMaxWidth');
        $this->maxHeight   = $config->get('URI', 'ImgCacheMaxHeight');
        return true;
    }
	
    public function filter(&$uri, $config, $context) {
        $scheme_obj = $uri->getSchemeObj($config, $context);
        if (!$scheme_obj) return true; // ignore unknown schemes, could be a local file or maybe another postfilter did it
        if (is_null($uri->host) || empty($scheme_obj->browsable))
            return true;
        $token = &$context->get('CurrentToken', true);
        if (($token->name != 'img') || ($context->get('CurrentAttr', true) != 'src') || (!$this->maxWidth) || (!$this->maxHeight))
            return true;
        $string = $uri->toString();
        $dest_pic_base_name = $this->microtime_hex();
        $dest_pic = $this->ScaleCacheImageFile($string, $this->path . $dest_pic_base_name, $this->maxWidth, $this->maxHeight);
        $new_uri = $this->parser->parse($dest_pic ? $dest_pic['picURI'] : '');
        $token->attr['width'] = $dest_pic['picWidth'];
        $token->attr['height'] = $dest_pic['picHeight'];
        // don't redirect if the target host is the same as the starting host
        // (prevents double-cacheing)
        if ($uri->host === $new_uri->host) return true;
        $uri = $new_uri; // overwrite
        return true;
    }

    protected function microtime_hex() {
        list($usec, $sec) = explode(" ", microtime());
        return dechex($sec) . dechex(10000*$usec);
    }

    protected function ScaleCacheImageFile($source_pic, $dest_pic_base_name, $max_width, $max_height) {
    // borrowed heavily from http://us2.php.net/manual/en/function.imagecreatefromjpeg.php#89865

        $imgInfo = getimagesize($source_pic);
        if (!$imgInfo) return false;
        list($width, $height, $image_type) = getimagesize($source_pic);

        switch ($image_type) {
            case 1: 
                $src = imagecreatefromgif($source_pic); 
                $dest_pic = $dest_pic_base_name . '.gif';
                break;
            case 2: 
                $src = imagecreatefromjpeg($source_pic);  
                $dest_pic = $dest_pic_base_name . '.jpg';
                break;
            case 3: 
                $src = imagecreatefrompng($source_pic); 
                $dest_pic = $dest_pic_base_name . '.png';
                break;
            default: 
                return false;  
                break;
        }

        $x_ratio = $max_width / $width;
        $y_ratio = $max_height / $height;

        if( ($x_ratio >= 1) && ($y_ratio >= 1) ) {
            $tmp = $src;
            $tn_width = $width;
            $tn_height = $height;
        } else {
            if ($x_ratio < $y_ratio) {
                $tn_height = ceil($x_ratio * $height);
                $tn_width = $max_width;
            } else {
                $tn_width = ceil($y_ratio * $width);
                $tn_height = $max_height;
            }

            $tmp = imagecreatetruecolor($tn_width,$tn_height);

            // Check if this image is PNG or GIF to preserve its transparency
            if(($image_type == 1) OR ($image_type==3)) {
                imagealphablending($tmp, false);
                imagesavealpha($tmp,true);
                $transparent = imagecolorallocatealpha($tmp, 255, 255, 255, 127);
                imagefilledrectangle($tmp, 0, 0, $tn_width, $tn_height, $transparent);
            }
		
            imagecopyresampled($tmp,$src,0,0,0,0,$tn_width, $tn_height,$width,$height);
        }

        switch ($image_type) {
            case 1: imagegif($tmp, $dest_pic); break;
            case 2: imagejpeg($tmp, $dest_pic, 100);  break; // best quality
            case 3: imagepng($tmp, $dest_pic, 0); break; // no compression
        }

        return array('picURI' => $dest_pic, 'picWidth' => $tn_width, 'picHeight' => $tn_height);
    }
}

