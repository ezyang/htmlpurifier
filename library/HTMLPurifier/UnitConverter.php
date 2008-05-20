<?php

/**
 * Class for converting between different unit-lengths as specified by
 * CSS.
 */
class HTMLPurifier_UnitConverter
{
    
    const ENGLISH = 1;
    const METRIC = 2;
    
    /**
     * Units information array. Units are grouped into measuring systems
     * (English, Metric), and are assigned an integer representing
     * the conversion factor between that unit and the smallest unit in
     * the system. Numeric indexes are actually magical constants that
     * encode conversion data from one system to the next, with a O(n^2)
     * constraint on memory (this is generally not a problem, since
     * the number of measuring systems is small.)
     */
    protected static $units = array(
        self::ENGLISH => array(
            'pt' => 1,
            'pc' => 12,
            'in' => 72,
            self::METRIC => array('pt', '0.352777778', 'mm'),
        ),
        self::METRIC => array(
            'mm' => 1,
            'cm' => 10,
            self::ENGLISH => array('mm', '2.83464567', 'pt'),
        ),
    );
    
    /**
     * Minimum bcmath precision for output.
     */
    protected $outputPrecision;
    
    /**
     * Bcmath precision for internal calculations.
     */
    protected $internalPrecision;
    
    public function __construct($output_precision = 4, $internal_precision = 10) {
        $this->outputPrecision = $output_precision;
        $this->internalPrecision = $internal_precision;
    }
    
    /**
     * Converts a length object of one unit into another unit.
     * @param HTMLPurifier_Length $length
     *      Instance of HTMLPurifier_Length to convert. You must validate()
     *      it before passing it here!
     * @param string $to_unit
     *      Unit to convert to.
     * @note
     *      About precision: This conversion function pays very special
     *      attention to the incoming precision of values and attempts
     *      to maintain a number of significant figure. Results are
     *      fairly accurate up to nine digits. Some caveats:
     *          - If a number is zero-padded as a result of this significant
     *            figure tracking, the zeroes will be eliminated.
     *          - If a number contains less than four sigfigs ($outputPrecision)
     *            and this causes some decimals to be excluded, those
     *            decimals will be added on.
     */
    public function convert($length, $to_unit) {
        
        if (!$length->isValid()) return false;
        
        $n    = $length->getN();
        $unit = $length->getUnit();
        
        if ($n === '0' || $unit === false) {
            return new HTMLPurifier_Length('0', false);
        }
        
        $state = $dest_state = false;
        foreach (self::$units as $k => $x) {
            if (isset($x[$unit])) $state = $k;
            if (isset($x[$to_unit])) $dest_state = $k;
        }
        if (!$state || !$dest_state) return false;
        
        // Some calculations about the initial precision of the number;
        // this will be useful when we need to do final rounding.
        $sigfigs = $this->getSigFigs($n);
        if ($sigfigs < $this->outputPrecision) $sigfigs = $this->outputPrecision;
        
        // BCMath's internal precision deals only with decimals. Use
        // our default if the initial number has no decimals, or increase
        // it by how ever many decimals, thus, the number of guard digits
        // will always be greater than or equal to internalPrecision.
        $log = (int) floor(log(abs($n), 10));
        $cp = ($log < 0) ? $this->internalPrecision - $log : $this->internalPrecision; // internal precision
        
        for ($i = 0; $i < 2; $i++) {
            
            // Determine what unit IN THIS SYSTEM we need to convert to
            if ($dest_state === $state) {
                // Simple conversion
                $dest_unit = $to_unit;
            } else {
                // Convert to the smallest unit, pending a system shift
                $dest_unit = self::$units[$state][$dest_state][0];
            }
            
            // Do the conversion if necessary
            if ($dest_unit !== $unit) {
                $factor = bcdiv(self::$units[$state][$unit], self::$units[$state][$dest_unit], $cp);
                $n = bcmul($n, $factor, $cp);
                $unit = $dest_unit;
            }
            
            // Output was zero, so bail out early. Shouldn't ever happen.
            if ($n === '') {
                $n = '0';
                $unit = $to_unit;
                break;
            }
            
            // It was a simple conversion, so bail out
            if ($dest_state === $state) {
                break;
            }
            
            if ($i !== 0) {
                // Conversion failed! Apparently, the system we forwarded
                // to didn't have this unit. This should never happen!
                return false;
            }
            
            // Pre-condition: $i == 0
            
            // Perform conversion to next system of units
            $n = bcmul($n, self::$units[$state][$dest_state][1], $cp);
            $unit = self::$units[$state][$dest_state][2];
            $state = $dest_state;
            
            // One more loop around to convert the unit in the new system.
            
        }
        
        // Post-condition: $unit == $to_unit
        if ($unit !== $to_unit) return false;
        
        // Calculate how many decimals we need ($rp)
        // Calculations will always be carried to the decimal; this is
        // a limitation with BC (we can't set the scale to be negative)
        $new_log = (int) floor(log(abs($n), 10));
        $rp = $sigfigs - $new_log - 1;
        $neg = $n < 0 ? '-' : '';
        
        // Useful for debugging:
        //echo "<pre>n";
        //echo "$n\nsigfigs = $sigfigs\nnew_log = $new_log\nlog = $log\nrp = $rp\n</pre>\n";
        
        if ($rp >= 0) {
            $n = bcadd($n, $neg . '0.' .  str_repeat('0', $rp) . '5', $rp + 1);
            $n = bcdiv($n, '1', $rp);
        } else {
            if ($new_log + 1 >= $sigfigs) {
                $n = bcadd($n, $neg . '5' . str_repeat('0', $new_log - $sigfigs));
                $n = substr($n, 0, $sigfigs + strlen($neg)) . str_repeat('0', $new_log + 1 - $sigfigs);
            }
        }
        if (strpos($n, '.') !== false) $n = rtrim($n, '0');
        $n = rtrim($n, '.');
        
        return new HTMLPurifier_Length($n, $unit);
    }
    
    /**
     * Returns the number of significant figures in a string number.
     * @param string $n Decimal number
     * @return int number of sigfigs
     */
    public function getSigFigs($n) {
        $n = ltrim($n, '0+-');
        $dp = strpos($n, '.'); // decimal position
        if ($dp === false) {
            $sigfigs = strlen(rtrim($n, '0'));
        } else {
            $sigfigs = strlen(ltrim($n, '0.')); // eliminate extra decimal character
            if ($dp !== 0) $sigfigs--;
        }
        return $sigfigs;
    }
    
}
