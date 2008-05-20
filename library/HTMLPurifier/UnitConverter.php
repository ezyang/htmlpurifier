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
     *          - Significant digits will be ignored for quantities greater
     *            than one. This is a limitation of BCMath and I don't
     *            feel like coding around it.
     */
    public function convert($length, $to_unit) {
        if ($length->n === '0' || $length->unit === false) {
            return new HTMLPurifier_Length('0', $unit);
        }
        
        $state = $dest = false;
        foreach (self::$units as $k => $x) {
            if (isset($x[$length->unit])) $state = $k;
            if (isset($x[$to_unit])) $dest_state = $k;
        }
        if (!$state || !$dest_state) return false;
        
        $n    = $length->n;
        $unit = $length->unit;
        
        // Some calculations about the initial precision of the number;
        // this will be useful when we need to do final rounding.
        $log = (int) floor(log($n, 10));
        if (strpos($n, '.') === false) {
            $sigfigs = strlen(trim($n, '0+-'));
        } else {
            $sigfigs = strlen(ltrim($n, '0+-')) - 1; // eliminate extra decimal character
        }
        if ($sigfigs < $this->outputPrecision) $sigfigs = $this->outputPrecision;
        
        // BCMath's internal precision deals only with decimals. Use
        // our default if the initial number has no decimals, or increase
        // it by how ever many decimals, thus, the number of guard digits
        // will always be greater than or equal to internalPrecision.
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
            
            // Output was zero, so bail out early
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
        $new_log = (int) floor(log($n, 10));
        
        $rp = $sigfigs - $new_log - $log - 1;
        if ($rp < 0) $rp = 0;
        
        $n = bcadd($n, '0.' .  str_repeat('0', $rp) . '5', $rp + 1);
        $n = bcdiv($n, '1', $rp);
        if (strpos($n, '.') !== false) $n = rtrim($n, '0');
        $n = rtrim($n, '.');
        
        return new HTMLPurifier_Length($n, $unit);
    }
    
}
