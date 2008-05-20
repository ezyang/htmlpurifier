<?php

class HTMLPurifier_UnitConverterTest extends HTMLPurifier_Harness
{
    
    protected function assertConversion($input, $expect) {
        $input = HTMLPurifier_Length::make($input);
        $expect = HTMLPurifier_Length::make($expect);
        $converter = new HTMLPurifier_UnitConverter();
        $result = $converter->convert($input, $expect->unit);
        $this->assertIdentical($result, $expect);
    }
    
    function testEnglish() {
        $this->assertConversion('1in', '6pc');
        $this->assertConversion('6pc', '1in');
        
        $this->assertConversion('1in', '72pt');
        $this->assertConversion('72pt', '1in');
        
        $this->assertConversion('1pc', '12pt');
        $this->assertConversion('12pt', '1pc');
        
        $this->assertConversion('1pt', '0.01389in');
        $this->assertConversion('1.000pt', '0.01389in');
        $this->assertConversion('100000pt', '1389in');
    }
    
    function testMetric() {
        $this->assertConversion('1cm', '10mm');
        $this->assertConversion('10mm', '1cm');
        $this->assertConversion('1mm', '0.1cm');
        $this->assertConversion('100mm', '10cm');
    }
    
    function testEnglishMetric() {
        $this->assertConversion('2.835pt', '1mm');
        $this->assertConversion('1mm', '2.835pt');
        $this->assertConversion('0.3937in', '1cm');
    }
    
}
