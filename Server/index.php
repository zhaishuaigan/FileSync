<?php

define('LIB_DIR', './lib/');
require_once LIB_DIR . 'config/config.php';
require_once LIB_DIR . 'class/App.class.php';

$pathinfo = filter_input(INPUT_SERVER, 'PATH_INFO');
$pathArray = explode('/', substr($pathinfo, 1), 2);
if (!$pathinfo || count($pathArray) != 2) {
    App::return404();
}
list($size, $filename) = $pathArray;
if (App::exists($filename, $size)) {
    App::readFile($filename, $size);
} else {
    App::return404();
}
