<?php
/*
 * jQuery File Upload Plugin PHP Class
 * https://github.com/blueimp/jQuery-File-Upload
 *
 * Copyright 2010, Sebastian Tschan
 * https://blueimp.net
 *
 * Licensed under the MIT license:
 * https://opensource.org/licenses/MIT
 */

class UploadHandler {
    protected $options;

    // PHP File Upload error message codes:
    // http://php.net/manual/en/features.file-upload.errors.php
    protected $error_messages = array(
        1                     => 'The uploaded file exceeds the upload_max_filesize directive in php.ini',
        2                     => 'The uploaded file exceeds the MAX_FILE_SIZE directive that was specified in the HTML form',
        3                     => 'The uploaded file was only partially uploaded',
        4                     => 'No file was uploaded',
        6                     => 'Missing a temporary folder',
        7                     => 'Failed to write file to disk',
        8                     => 'A PHP extension stopped the file upload',
        'post_max_size'       => 'The uploaded file exceeds the post_max_size directive in php.ini',
        'max_file_size'       => 'File is too big. Max size is 800kB.',
        'min_file_size'       => 'File is too small',
        'accept_file_types'   => 'Filetype not allowed',
        'max_number_of_files' => 'Maximum number of files exceeded',
        'abort'               => 'File upload aborted',
        'waf'                 => 'Filename invalid'
    );

    protected $image_objects = array();

    public function __construct($options = null, $initialize = true,
                               $error_messages = null) {
        $this->response = array();
        $this->options  = array(
            'upload_dir'                       => dirname($this->get_server_var('SCRIPT_FILENAME')) . '/uploads/',
            'upload_url'                       => 'uploads/',
            'input_stream'                     => 'php://input',
            'user_dirs'                        => false,
            'mkdir_mode'                       => 0755,
            'param_name'                       => 'files',
            'access_control_allow_origin'      => '*',
            'access_control_allow_credentials' => false,
            'access_control_allow_methods'     => array(
                'POST'
            ),
            'access_control_allow_headers'     => array(
                'Content-Type',
                'Content-Disposition'
            ),
            // Read files in chunks to avoid memory limits when download_via_php
            // is enabled, set to 0 to disable chunked reading of files:
            'readfile_chunk_size'              => 10 * 1024 * 1024, // 10 MiB
            // Defines which files (based on their names) are accepted for upload:
            'accept_file_types'                => '/\.(gif|jpe?g|png)$/i',
            'accept_file_name'                 => '/^[\w-]+\.(gif|jpe?g|png)$/i',
            // The php.ini settings upload_max_filesize and post_max_size
            // take precedence over the following max_file_size setting:
            'max_file_size'                    => 819200,
            'min_file_size'                    => 1,
            // Set the following option to false to enable resumable uploads:
            'discard_aborted_uploads'          => true,
            'print_response'                   => true
        );
        if ($options) {
            $this->options = $options + $this->options;
        }
        if ($error_messages) {
            $this->error_messages = $error_messages + $this->error_messages;
        }
        if ($initialize) {
            $this->initialize();
        }
    }

    protected function initialize() {
        switch ($this->get_server_var('REQUEST_METHOD')) {
            case 'POST':
                $this->post($this->options['print_response']);
                break;
            default:
                $this->header('HTTP/1.1 405 Method Not Allowed');
        }
    }

    protected function get_user_id() {
        @session_start();
        return session_id();
    }

    protected function get_user_path() {
        if ($this->options['user_dirs']) {
            return $this->get_user_id() . '/';
        }
        return '';
    }

    protected function get_upload_path($file_name = null, $version = null) {
        $file_name = $file_name ? $file_name : '';
        return $this->options['upload_dir'] . $this->get_user_path() . $file_name;
    }

    protected function generate_upload_path() {
        return $this->options['upload_dir'] . $this->get_user_path() . time() . $this->generate_password() . ".png";
    }

    protected function get_query_separator($url) {
        return strpos($url, '?') === false ? '?' : '&';
    }

    protected function get_download_url($file_name, $version = null, $direct = false) {
        return $this->options['upload_url'] . $this->get_user_path() . rawurlencode($file_name);
    }

    // Fix for overflowing signed 32 bit integers,
    // works for sizes up to 2^32-1 bytes (4 GiB - 1):
    protected function fix_integer_overflow($size) {
        if ($size < 0) {
            $size += 2.0 * (PHP_INT_MAX + 1);
        }
        return $size;
    }

    protected function get_file_size($file_path, $clear_stat_cache = false) {
        if ($clear_stat_cache) {
            if (version_compare(PHP_VERSION, '5.3.0') >= 0) {
                clearstatcache(true, $file_path);
            } else {
                clearstatcache();
            }
        }
        return $this->fix_integer_overflow(filesize($file_path));
    }

    protected function is_valid_file_object($file_name) {
        $file_path = $this->get_upload_path($file_name);
        if (is_file($file_path) && $file_name[0] !== '.') {
            return true;
        }
        return false;
    }

    protected function get_file_object($file_name) {
        if ($this->is_valid_file_object($file_name)) {
            $file       = new \stdClass();
            $file->name = $file_name;
            $file->size = $this->get_file_size($this->get_upload_path($file_name));
            $file->url = $this->get_download_url($file->name);
            return $file;
        }
        return null;
    }

    protected function get_file_objects($iteration_method = 'get_file_object') {
        $upload_dir = $this->get_upload_path();
        if (!is_dir($upload_dir)) {
            return array();
        }
        return array_values(array_filter(array_map(
            array($this, $iteration_method),
            scandir($upload_dir)
        )));
    }

    protected function count_file_objects() {
        return count($this->get_file_objects('is_valid_file_object'));
    }

    protected function get_error_message($error) {
        return isset($this->error_messages[$error]) ? $this->error_messages[$error] : $error;
    }

    public function get_config_bytes($val) {
        $val  = trim($val);
        $last = strtolower($val[strlen($val) - 1]);
        $val  = (int) $val;
        switch ($last) {
            case 'g':
                $val *= 1024;
            case 'm':
                $val *= 1024;
            case 'k':
                $val *= 1024;
        }
        return $this->fix_integer_overflow($val);
    }

    protected function waf($filename) {
        if (!preg_match($this->options['accept_file_name'], $filename)) {
            return false;
        }
        return true;
    }

    protected function validate($uploaded_file, $file, $error, $index) {
        if ($error) {
            $file->error = $this->get_error_message($error);
            return false;
        }
        $content_length = $this->fix_integer_overflow(
            (int) $this->get_server_var('CONTENT_LENGTH')
        );
        $post_max_size = $this->get_config_bytes(ini_get('post_max_size'));
        if ($post_max_size && ($content_length > $post_max_size)) {
            $file->error = $this->get_error_message('post_max_size');
            return false;
        }
        if (!preg_match($this->options['accept_file_types'], $file->name)) {
            $file->error = $this->get_error_message('accept_file_types');
            return false;
        }
        if ($uploaded_file && is_uploaded_file($uploaded_file)) {
            $file_size = $this->get_file_size($uploaded_file);
        } else {
            $file_size = $content_length;
        }
        if ($this->options['max_file_size'] && ($file_size > $this->options['max_file_size'] || $file->size > $this->options['max_file_size'])) {
            $file->error = $this->get_error_message('max_file_size');
            return false;
        }
        if ($this->options['min_file_size'] && $file_size < $this->options['min_file_size']) {
            $file->error = $this->get_error_message('min_file_size');
            return false;
        }

        return true;
    }

    protected function upcount_name_callback($matches) {
        $index = isset($matches[1]) ? ((int) $matches[1]) + 1 : 1;
        $ext   = isset($matches[2]) ? $matches[2] : '';
        return ' (' . $index . ')' . $ext;
    }

    protected function upcount_name($name) {
        return preg_replace_callback(
            '/(?:(?: \(([\d]+)\))?(\.[^.]+))?$/',
            array($this, 'upcount_name_callback'),
            $name,
            1
        );
    }

    protected function trim_file_name($file_path, $name, $size, $type, $error, $index, $content_range) {
        // Remove path information and dots around the filename, to prevent uploading
        // into different directories or replacing hidden system files.
        // Also remove control characters and spaces (\x00..\x20) around the filename:
        $name = trim($this->basename(stripslashes($name)),
                                ".\x00..\x20");
        // Use a timestamp for empty filenames:
        if (!$name) {
            $name = str_replace('.', '-', microtime(true));
        }
        return $name;
    }

    protected function get_file_name($file_path, $name, $size, $type, $error, $index, $content_range) {
        $name = $this->trim_file_name($file_path, $name, $size, $type, $error, $index, $content_range);
        return $name;
    }

    protected function handle_file_upload($uploaded_file, $name, $size, $type, $error, $index = null, $content_range = null) {
        if ($this->waf($name)) {
            $file       = new \stdClass();
            $file->name = $this->get_file_name($uploaded_file, $name, $size, $type, $error, $index, $content_range);
            $file->size = $this->fix_integer_overflow((int) $size);
            if ($this->validate($uploaded_file, $file, $error, $index)) {
                $this->handle_form_data($file, $index);
                $upload_dir = $this->get_upload_path();
                if (!is_dir($upload_dir)) {
                    mkdir($upload_dir, $this->options['mkdir_mode'], true);
                }

                $new_file_name  = time() . $this->generate_password();
                $file_extension = strrchr($name, ".");
                if ($file_extension === ".jpg" || $file_extension === ".jpeg" || $file_extension === ".png" || $file_extension === ".gif") {
                    $new_file_name = $new_file_name . $file_extension;
                }

                $file_path = $this->get_upload_path($new_file_name);

                $append_file = $content_range && is_file($file_path) && $file->size > $this->get_file_size($file_path);
                if ($append_file) {
                    die("Something wrong!");
                }

                if ($uploaded_file && is_uploaded_file($uploaded_file)) {
                    // multipart/formdata uploads (POST method uploads)
                    if ($append_file) {
                        file_put_contents(
                            $file_path,
                            fopen($uploaded_file, 'r'),
                            FILE_APPEND
                        );
                    } else {
                        move_uploaded_file($uploaded_file, $file_path);
                    }
                } else {
                    // Non-multipart uploads (PUT method support)
                    file_put_contents(
                        $file_path,
                        fopen($this->options[
                             'input_stream'], 'r'),
                        $append_file ? FILE_APPEND : 0
                    );
                }
                $file_size = $this->get_file_size($file_path, $append_file);
                if ($file_size === $file->size) {
                    $file->url = $this->get_download_url($new_file_name);
                } else {
                    $file->size = $file_size;
                    if (!$content_range && $this->options['discard_aborted_uploads']) {
                        unlink($file_path);
                        $file->error = $this->get_error_message('abort');
                    }
                }
            }
            if (isset($file->name)) {
                $file->name = htmlspecialchars($file->name, ENT_QUOTES, 'UTF-8');
            }
        } else {
            $file        = new \stdClass();
            $file->error = $this->get_error_message('waf');
        }
        return $file;
    }

    protected function readfile($file_path) {
        $file_size  = $this->get_file_size($file_path);
        $chunk_size = $this->options['readfile_chunk_size'];
        if ($chunk_size && $file_size > $chunk_size) {
            $handle = fopen($file_path, 'rb');
            while (!feof($handle)) {
                echo fread($handle, $chunk_size);
                @ob_flush();
                @flush();
            }
            fclose($handle);
            return $file_size;
        }
        return readfile($file_path);
    }

    protected function body($str) {
        echo $str;
    }

    protected function header($str) {
        header($str);
    }

    protected function get_upload_data($id) {
        return @$_FILES[$id];
    }

    protected function get_post_param($id) {
        return @$_POST[$id];
    }

    protected function get_query_param($id) {
        return @$_GET[$id];
    }

    protected function get_server_var($id) {
        return @$_SERVER[$id];
    }

    protected function handle_form_data($file, $index) {
        // Handle form data, e.g. $_POST['description'][$index]
    }

    protected function get_version_param() {
        return $this->basename(stripslashes($this->get_query_param('version')));
    }

    protected function get_singular_param_name() {
        return substr($this->options['param_name'], 0, -1);
    }

    protected function get_file_name_param() {
        $name = $this->get_singular_param_name();
        return $this->basename(stripslashes($this->get_query_param($name)));
    }

    protected function get_file_names_params() {
        $params = $this->get_query_param($this->options['param_name']);
        if (!$params) {
            return null;
        }
        foreach ($params as $key => $value) {
            $params[$key] = $this->basename(stripslashes($value));
        }
        return $params;
    }

    protected function get_file_type($file_path) {
        switch (strtolower(pathinfo($file_path, PATHINFO_EXTENSION))) {
            case 'jpeg':
            case 'jpg':
                return 'image/jpeg';
            case 'png':
                return 'image/png';
            case 'gif':
                return 'image/gif';
            default:
                return '';
        }
    }

    protected function send_content_type_header() {
        $this->header('Vary: Accept');
        if (strpos($this->get_server_var('HTTP_ACCEPT'), 'application/json') !== false) {
            $this->header('Content-type: application/json');
        } else {
            $this->header('Content-type: text/plain');
        }
    }

    protected function send_access_control_headers() {
        //$this->header('Access-Control-Allow-Origin: '.$this->options['access_control_allow_origin']);
        $this->header('Access-Control-Allow-Credentials: ' . ($this->options['access_control_allow_credentials'] ? 'true' : 'false'));
        $this->header('Access-Control-Allow-Methods: ' . implode(', ', $this->options['access_control_allow_methods']));
        $this->header('Access-Control-Allow-Headers: ' . implode(', ', $this->options['access_control_allow_headers']));
    }

    public function generate_response($content, $print_response = true) {
        $this->response = $content;
        if ($print_response) {
            $json = json_encode($content);
            $this->head();
            if ($this->get_server_var('HTTP_CONTENT_RANGE')) {
                $files = isset($content[$this->options['param_name']]) ?
                $content[$this->options['param_name']] : null;
                if ($files && is_array($files) && is_object($files[0]) && $files[0]->size) {
                    $this->header('Range: 0-' . ($this->fix_integer_overflow((int) $files[0]->size) - 1));
                }
            }
            $this->body($json);
        }
        return $content;
    }

    public function get_response() {
        return $this->response;
    }

    public function head() {
        $this->header('Pragma: no-cache');
        $this->header('Cache-Control: no-store, no-cache, must-revalidate');
        $this->header('Content-Disposition: inline; filename="files.json"');
        // Prevent Internet Explorer from MIME-sniffing the content-type:
        $this->header('X-Content-Type-Options: nosniff');
        if ($this->options['access_control_allow_origin']) {
            $this->send_access_control_headers();
        }
        $this->send_content_type_header();
    }

    public function post($print_response = true) {
        $upload = $this->get_upload_data($this->options['param_name']);
        // Parse the Content-Disposition header, if available:
        $content_disposition_header = $this->get_server_var('HTTP_CONTENT_DISPOSITION');
        $file_name                  = $content_disposition_header ?
        rawurldecode(preg_replace(
            '/(^[^"]+")|("$)/',
            '',
            $content_disposition_header
        )) : null;
        // Parse the Content-Range header, which has the following form:
        // Content-Range: bytes 0-524287/2000000
        //$content_range_header = $this->get_server_var('HTTP_CONTENT_RANGE');
        $content_range = null; //$content_range_header ?
        //    preg_split('/[^0-9]+/', $content_range_header) : null;
        $size  = null; //$content_range ? $content_range[3] : null;
        $files = array();
        if ($upload) {
            if (is_array($upload['tmp_name'])) {
                // param_name is an array identifier like "files[]",
                // $upload is a multi-dimensional array:

                foreach ($upload['tmp_name'] as $index =>$value) {
                    $files = $this->handle_file_upload(
                        $upload['tmp_name'][$index],
                        $file_name ? $file_name :
                               $upload['name'][$index],
                        $size ? $size : $upload['size']
                                      [$index],
                        $upload['type'][$index],
                        $upload['error'][$index],
                        $index,
                        $content_range
                    );
                    break;
                }
            } else {
                // param_name is a single object identifier like "file",
                // $upload is a one-dimensional array:
                $files = $this->handle_file_upload(
                    isset($upload['tmp_name']) ? $upload['tmp_name'] : null,
                    $file_name ? $file_name : (isset($upload['name']) ? $upload['name'] : null),
                    $size ? $size : (isset($upload['size']) ? $upload['size'] : $this->get_server_var('CONTENT_LENGTH')),
                    isset($upload['type']) ? $upload['type'] : $this->get_server_var('CONTENT_TYPE'),
                    isset($upload['error']) ? $upload['error'] : null,
                    null,
                    $content_range
                );
            }
        }
        $response = array($this->options['param_name'] => $files);
        return $this->generate_response($response, $print_response);
    }

    protected function basename($filepath, $suffix = null) {
        $splited = preg_split('/\//', rtrim($filepath, '/ '));
        return substr(basename('X' . $splited[count($splited) - 1], $suffix), 1);
    }

    protected function generate_password($length = 8) {
        $chars    = "abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789";
        $password = "";
        for ($i = 0; $i < $length; $i++) {
            $password .= $chars[mt_rand(0, strlen($chars) - 1)];
        }

        return $password;
    }
}

$upload_handler = new UploadHandler();
