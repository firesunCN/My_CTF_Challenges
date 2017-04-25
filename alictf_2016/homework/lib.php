<?php
function clean_input($dirty) {
    if (get_magic_quotes_gpc()) {
        $clean = mysql_real_escape_string(stripslashes($dirty));
    } else {
        $clean = mysql_real_escape_string($dirty);
    }
    return $clean;
}