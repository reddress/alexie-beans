<?php 
// convert an amount with cents to whole number of cents (integer)
// separator may be a comma or period.
function parse_amount($amount_str) {
    $amount_str = str_replace(",", ".", $amount_str);

    // separator is first character
    if (substr($amount_str, 0, 1) === ".") {
        $amount_str = "0" . $amount_str;
    }

    // no separator
    if (!strpos($amount_str, ".")) {
        return ((int) $amount_str) * 100;
    }

    $parts = explode(".", $amount_str);
    $whole_str = $parts[0];
    $cents_str = $parts[1];

    if (strlen($cents_str) > 2) {
        $cents_str = $substr($cents_str, 0, 2);
    }

    if (strlen($cents_str) === 0) {
        $cents_str .= "00";
    }   

    if (strlen($cents_str) === 1) {
        $cents_str .= "0";
    }
        
    return ((int) $whole_str) * 100 + ((int) $cents_str);
}

function addThousands($amount) {
    $rev = strrev($amount);
    $out = "";
    while (strlen($rev) > 3) {
        $out .= substr($rev, 0, 3);
        $out .= ".";
        $rev = substr($rev, 3);
    }
    $out .= $rev;
    return strrev($out);
}

// convert integral cents value to whole amount separated from cents
function separate_amount($amount) {
    $output = (string) $amount;

    $amount_cents = (int) $amount;
    $sign = ($amount_cents < 0) ? "-" : "";

    if ($sign === "-") {
        $output = substr($output, 1);
    }

    if (strlen($output) < 2) {
        $output = "0" . $output;
    }

    if (strlen($output) < 3) {
        $output = "0" . $output;
    }

    $digits = strlen($output);
    return $sign . addThousands(substr($output, 0, $digits-2)) . "." . substr($output, $digits-2);
}

?>
