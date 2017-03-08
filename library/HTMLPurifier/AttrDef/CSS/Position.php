<?php

/**
 * Validates the value of position.
 */
class HTMLPurifier_AttrDef_CSS_Position extends HTMLPurifier_AttrDef
{

    /**
     * @param string $string
     * @param HTMLPurifier_Config $config
     * @param HTMLPurifier_Context $context
     * @return bool|string
     */
    public function validate($string, $config, $context)
    {
        $string = $this->parseCDATA($string);
        $bits = explode(' ', $string);

        $keywords = array();
        $keywords['h'] = false; // left, right
        $keywords['v'] = false; // top, bottom
        $keywords['ch'] = false; // center (first word)
        $keywords['cv'] = false; // center (second word)

        $i = 0;

        $lookup = array(
            'top' => 'v',
            'bottom' => 'v',
            'left' => 'h',
            'right' => 'h',
            'center' => 'c'
        );

        foreach ($bits as $bit) {
            if ($bit === '') {
                continue;
            }

            // test for keyword
            $lbit = ctype_lower($bit) ? $bit : strtolower($bit);
            if (!isset($lookup[$lbit])) {
                return false;
            }

            $status = $lookup[$lbit];
            if ($status == 'c') {
                if ($i == 0) {
                    $status = 'ch';
                } else {
                    $status = 'cv';
                }
            }
            $keywords[$status] = $lbit;
            $i++;
        }

        if (!$i) {
            return false;
        } // no valid values were caught

        $ret = array();

        // first keyword
        if ($keywords['h']) {
            $ret[] = $keywords['h'];
        } elseif ($keywords['ch']) {
            $ret[] = $keywords['ch'];
            $keywords['cv'] = false; // prevent re-use: center = center center
        }

        if ($keywords['v']) {
            $ret[] = $keywords['v'];
        } elseif ($keywords['cv']) {
            $ret[] = $keywords['cv'];
        }

        if (empty($ret)) {
            return false;
        }
        return implode(' ', $ret);
    }
}
