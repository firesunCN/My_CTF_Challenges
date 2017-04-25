<?php
if (isset($_POST['url']) && $_POST['url'] != "") {
    header('Content-type: application/json');
    $file = new \stdClass();

    $url = $_POST['url'];
    if (strlen($url) < 200) {
        if (waf($url)) {
            $url       = escapeshellarg($url);
            $file_name = time() . generate_password();
            $tmp_path  = "/tmp/" . $file_name;
            system("curl -s -k --connect-timeout 5 -m 20 " . $url . " > " . $tmp_path);
            if (file_exists($tmp_path)) {
                $file->size = fix_integer_overflow(filesize($tmp_path));
                if ($file->size < 819200) {
                    $exif       = @exif_imagetype($tmp_path);
                    $image_info = @getimagesize($tmp_path);

                    if ($exif && ($exif === 1 || $exif === 2 || $exif === 3) && $image_info && $image_info[0] && $image_info[1] && $image_info[0] > 0 && $image_info[1] > 0) {
                        switch ($exif) {
                            case 1:
                                $src_func    = 'imagecreatefromgif';
                                $write_func  = 'imagegif';
                                $file_suffix = ".gif";
                                break;
                            case 2:
                                $src_func    = 'imagecreatefromjpeg';
                                $write_func  = 'imagejpeg';
                                $file_suffix = ".jpg";
                                break;
                            case 3:
                                $src_func    = 'imagecreatefrompng';
                                $write_func  = 'imagepng';
                                $file_suffix = ".png";
                                break;
                            default:
                                die();
                        }

                        $src_img  = @$src_func($tmp_path);
                        $out_path = dirname($_SERVER['SCRIPT_FILENAME']) . '/uploads/' . $file_name . $file_suffix;
                        @$write_func($src_img, $out_path);
                        @imagedestroy($src_img);

                        if (file_exists($out_path)) {
                            $file->url = 'uploads/'. $file_name . $file_suffix;
                        } else {
                            $file->error = "Not Image";
                        }
                    } else {
                        $file->error = "Not Image";
                    }
                } else {
                    $file->error = "File is too big. Max size is 800kB.";
                }
                @unlink($tmp_path);
            } else {
                $file->error = "Network Problem";
            }
        } else {
            $file->error = "Invalid URL";
        }
    } else {
        $file->error = "URL is too long";
    }

    $response = array("files" => $file);
    echo json_encode($response);
}

function generate_password($length = 8) {
    $chars    = "abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789";
    $password = "";
    for ($i = 0; $i < $length; $i++) {
        $password .= $chars[mt_rand(0, strlen($chars) - 1)];
    }

    return $password;
}

function waf($url) {
    if (stripos($url, " ") !== FALSE)
        return false;
    if (stripos($url, "\"") !== FALSE)
        return false;
    if (stripos($url, "'") !== FALSE)
        return false;
    if (stripos($url, ">") !== FALSE)
        return false;
    if (stripos($url, "<") !== FALSE)
        return false;
    if (stripos($url, "$") !== FALSE)
        return false;
    if (stripos($url, "|") !== FALSE)
        return false;
    if (stripos($url, "#") !== FALSE)
        return false;
    if (stripos($url, ";") !== FALSE)
        return false;
    if (stripos($url, "`") !== FALSE)
        return false;
    if (stripos($url, "127.0.0.1") !== FALSE)
        return false;
    if (stripos($url, "localhost") !== FALSE)
        return false;
    if (stripos($url, "2130706433") !== FALSE)
        return false;
    if (stripos($url, "017700000001") !== FALSE)
        return false;
    if (stripos($url, "0x7f000001") !== FALSE)
        return false;

    $regex = "/^https?:\/\/([\w\-\.:])+\/([\w\-!&@%\^\(\)\+=\[\]\{\},\.]+\/)*([\w\-!&@%\^\(\)\+=\[\]\{\},\.\?]+)$/";
    if (!preg_match($regex, $url))
        return false;
    
    return true;
}

function fix_integer_overflow($size) {
    if ($size < 0) {
        $size += 2.0 * (PHP_INT_MAX + 1);
    }
    return $size;
}
