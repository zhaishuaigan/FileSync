<?php

class App {

    public static function readFile($filename, $size) {
        if (self::exists($filename, $size)) {
            self::echoFile($filename, $size);
        } else {
            self::return404();
        }
    }

    public static function echoFile($filename, $size) {
        $config = $GLOBALS['config'];
        if ($size == $config['src_size']) {
            $filename = $config['static'] . '/' . $filename;
        } else {
            $filename = $config['static_thumb'] . $size . '/' . $filename;
        }
        $ext = substr(strrchr($filename, '.'), 1);
        header('Content-Type:image/' . $ext);
        readfile($filename);
    }

    // 检测文件是否存在
    public static function exists($filename, $size) {
        $config = $GLOBALS['config'];
        $wh = isset($config['size'][$size]) ? $config['size'][$size] : false;
        $srcFileName = $config['static'] . $filename;
        $thumbFileName = $config['static_thumb'] . $size . '/' . $filename;

        if (!file_exists($srcFileName) && self::serverFileExists($filename)) {
            self::getServerFile($filename);
        }
        if (file_exists($srcFileName)) {
            if ($size == $config['src_size']) {
                return true;
            }
            if (!$wh && !$config['autosize']) {
                return false;
            }
            if ($config['autosize'] && !preg_match('/^(\d+)x(\d+)$/', $size) && !$wh) {
                return false;
            }
            if (!file_exists($thumbFileName)) {
                App::thumb($filename, $size);
            }
            return true;
        }
        return false;
    }

    public static function serverFileExists($filename) {
        $serverList = $GLOBALS['config']['server_list'];
        $server = $serverList[array_rand($serverList)];
        $url = $server . '/' . $filename . '?check=true';
        $result = json_decode(file_get_contents($url));
        if ($result && $result->isfile) {
            return true;
        } else {
            return false;
        }
    }

    // 获取远程文件到本地
    public static function getServerFile($filename) {
        $dir = dirname($GLOBALS['config']['static'] . $filename);
        self::cmkdir($dir);
        $serverList = $GLOBALS['config']['server_list'];
        $url = $serverList[array_rand($serverList)] . '/' . $filename;
        $img = @file_get_contents($url);
        if ($img) {
            $srcFileName = $GLOBALS['config']['static'] . $filename;
            file_put_contents($srcFileName, $img, LOCK_EX);
        }
        return $img;
    }

    // 检测并创建目录
    public static function cmkdir($path) {
        if (is_dir($path)) {
            return;
        }
        $adir = explode('/', $path);
        $dirlist = '';
        $rootdir = array_shift($adir);
        if (($rootdir != '.' || $rootdir != '') && !file_exists($rootdir)) {
            @mkdir($rootdir);
        }
        foreach ($adir as $val) {
            $dirlist .= '/' . $val;
            $dirpath = $rootdir . $dirlist;
            if (!file_exists($dirpath)) {
                @mkdir($dirpath);
            }
        }
    }

    // 生成缩略图
    public static function thumb($filename, $size) {
        $config = $GLOBALS['config'];
        $wh = isset($config['size'][$size]) ? $config['size'][$size] : false;
        list($w, $h) = ($wh ? explode('x', $wh) : explode('x', $size));
        $srcname = $config['static'] . $filename;
        $thumbname = $config['static_thumb'] . $size . '/' . $filename;
        self::cmkdir(dirname($thumbname));
        require_once LIB_DIR . 'class/Image.class.php';
        $img = new Image();
        $img->thumb($srcname, $thumbname, null, (int) $w, (int) $h);
    }

    // 返回404
    public static function return404() {
        header('HTTP/1.1 404 Not Found');
        header("status: 404 Not Found");
        die();
    }

}
