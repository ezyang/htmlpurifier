<?php

class HTMLPurifier_SimpleTest_Reporter extends HTMLReporter
{
    
    function paintHeader($test_name) {
        parent::paintHeader($test_name);
        $test_file = $GLOBALS['HTMLPurifierTest']['File'];
?>
<form action="" method="get" id="select">
    <select name="f">
        <option value="" style="font-weight:bold;"<?php if(!$test_file) {echo ' selected';} ?>>All Tests</option>
        <?php foreach($GLOBALS['HTMLPurifierTest']['Files'] as $file) { ?>
            <option value="<?php echo $file ?>"<?php
                if ($test_file == $file) echo ' selected';
            ?>><?php echo $file ?></option>
        <?php } ?>
    </select>
    <input type="submit" value="Go">
</form>
<?php
        flush();
    }
    
    function _getCss() {
        $css = parent::_getCss();
        $css .= '
        #select {position:absolute;top:0.2em;right:0.2em;}
        ';
        return $css;
    }
    
}

