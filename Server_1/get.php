<?php

define('LIB_DIR', './lib/');
require_once LIB_DIR . 'config/config.php';
require_once LIB_DIR . 'class/App.class.php';

$filename = filter_input(INPUT_SERVER, 'PATH_INFO');
$check = filter_input(INPUT_GET, 'check');

if (!$filename) {
    App::return404();
}

if ($check) {
    $result = array('isfile' => 0);
    if (App::exists($filename, $GLOBALS['config']['src_size'])) {
        $result = array('isfile' => 1);
    }
    echo json_encode($result);
} else {
    if (App::exists($filename, $GLOBALS['config']['src_size'])) {
        App::readFile($filename, $GLOBALS['config']['src_size']);
    } else {
        App::return404();
    }
}
