<?php

define('LIB_DIR', './lib/');
require LIB_DIR . 'config/config.php';
require LIB_DIR . 'class/App.class.php';
$filename = filter_input(INPUT_SERVER, 'PATH_INFO');
$result = array(
    'success' => 0,
    'filename' => '',
    'msg' => '无同步文件!'
);
if ($filename) {
    $filename = substr($filename, 1);
    $info = App::uploadFile('file', $filename);
    if ($info['success']) {
        $result = array(
            'success' => 1,
            'filename' => $info['filename'],
            'msg' => '同步成功!'
        );
    } else {
        $result = array(
            'success' => 0,
            'filename' => '',
            'msg' => '同步失败!'
        );
    }
}
echo str_replace('\/', '/', json_encode($result));
