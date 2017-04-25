<?php
function encrypt($string) {
    $pass = "密码怎么会告诉你1234";
    $algorithm = 'rijndael-128';
    $key       = md5($pass, true);
    $iv_length = mcrypt_get_iv_size($algorithm, MCRYPT_MODE_CBC);
    $iv        = mcrypt_create_iv($iv_length, MCRYPT_RAND);
    $encrypted = mcrypt_encrypt($algorithm, $key, $string, MCRYPT_MODE_CBC, $iv);
    $result    = base64url_encode($iv . $encrypted);
    return $result;
}

function decrypt($string) {
    $pass = "密码怎么会告诉你1234";
    $algorithm = 'rijndael-128';
    $key       = md5($pass, true);
    $iv_length = mcrypt_get_iv_size($algorithm, MCRYPT_MODE_CBC);
    $string    = base64url_decode($string);
    $iv        = substr($string, 0, $iv_length);
    $encrypted = substr($string, $iv_length);
    $result    = mcrypt_decrypt($algorithm, $key, $encrypted, MCRYPT_MODE_CBC, $iv);
    $result    = rtrim($result, "\0");
    if (!preg_match("/^\w+$/", $result)) {
        $result = "";
    }
    return $result;
}

function base64url_encode($data) {
    return rtrim(strtr(base64_encode($data), '+/', '-_'), '=');
}

function base64url_decode($data) {
    return base64_decode(str_pad(strtr($data, '-_', '+/'), strlen($data) % 4, '=', STR_PAD_RIGHT));
}
?>