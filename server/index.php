<?php

define('LIB_DIR', './lib/');
require LIB_DIR . 'class/App.class.php';
$filename = isset($_SERVER['PATH_INFO']) ? $_SERVER['PATH_INFO'] : null;
$check = isset($_GET['check']) ? $_GET['check'] : false;
if ($check) {
    $result = array('isfile' => 1);
    if (App::exists($filename)) {
        $result = array('isfile' => 1);
    }
    echo json_encode($result);
} else {
    $w = isset($_GET['w']) ? $_GET['w'] : 0;
    $h = isset($_GET['h']) ? $_GET['h'] : 0;
    App::echoFile($filename, $w, $h);
}
