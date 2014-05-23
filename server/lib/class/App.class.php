<?php

$GLOBALS['config'] = require LIB_DIR . 'config/config.php';

class App {

    // 检测文件是否存在
    public static function exists($filename) {
        return file_exists($GLOBALS['config']['static'] . $filename);
    }

    public static function serverFileExists($filename) {
        $serverList = $GLOBALS['config']['server_list'];
        $server = $serverList[array_rand($serverList)];
        $url = $server . 'index.php' . $filename . '?check=true';
        $result = @json_decode(file_get_contents($url));
        if ($result && $result->isfile) {
            return true;
        } else {
            return false;
        }
    }

    // 获取远程文件到本地
    public static function getServerFile($filename) {
        $dir = dirname($GLOBALS['config']['static'] . $filename);
        if (!is_dir($dir)) {
            self::mkdirs($dir);
        }
        $serverList = $GLOBALS['config']['server_list'];
        $url = $serverList[array_rand($serverList)] . 'index.php' . $filename;
        $img = file_get_contents($url);
        if ($img) {
            file_put_contents($GLOBALS['config']['static'] . $filename, $img, LOCK_EX);
        }
        return $img;
    }

    // 上传文件
    public static function uploadFile($file, $filename = '') {
        $info = array(
            'success' => 0,
            'msg' => '未知错误!',
            'filename' => ''
        );
        $ext = strrchr($_FILES[$file]['name'], '.');
        if (!$filename) {
            $filename = date('Y/m/d') . '/' . self::mkguid() . $ext;
        }
        $dir = dirname($GLOBALS['config']['static'] . $filename);
        if (!is_dir($dir)) {
            self::mkdirs($dir);
        }
        if (!move_uploaded_file($_FILES[$file]['tmp_name'], $GLOBALS['config']['static'] . $filename)) {
            $info['success'] = 0;
            $info['msg'] = '文件上传保存错误！';
        } else {
            $info['success'] = 1;
            $info['msg'] = '文件上传成功';
            $info['filename'] = $filename;
        }
        return $info;
    }

    // 同步文件方法
    public static function fileSync($server, $filename) {
        $ch = curl_init();
        $post_data = array(
            'file' => '@' . $GLOBALS['config']['static'] . $filename
        );
        curl_setopt($ch, CURLOPT_HEADER, false);
        //启用时会发送一个常规的POST请求，类型为：application/x-www-form-urlencoded，就像表单提交的一样。
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_BINARYTRANSFER, true);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $post_data);
        curl_setopt($ch, CURLOPT_URL, $server . 'sync.php' . $filename);
        $info = curl_exec($ch);
        curl_close($ch);
        return json_decode($info);
    }

    // 将本地文件发给客户端
    public static function echoFile($filename) {
        $baseDir = $GLOBALS['config']['static'];
        $localfile = str_replace('//', '/', $baseDir . $filename);
        if (file_exists($localfile)) {
            header('Content-Type:image/' . substr(strrchr($filename, '.'), 1));
            readfile($localfile);
        } else {
            $isfile = self::serverFileExists($filename);
            if ($GLOBALS['config']['mode'] == 'Slave' && $isfile) {
                self::getServerFile($filename);
                self::echoFile($filename);
            } else {
                header('HTTP/1.1 404 Not Found');
                header("status: 404 Not Found");
            }
        }
    }

    // 循环创建目录
    public static function mkdirs($path) {
        $adir = explode('/', $path);
        $dirlist = '';
        $rootdir = array_shift($adir);
        if (($rootdir != '.' || $rootdir != '') && !file_exists($rootdir)) {
            @mkdir($rootdir);
        }
        foreach ($adir as $key => $val) {
            $dirlist .= "/" . $val;
            $dirpath = $rootdir . $dirlist;
            if (!file_exists($dirpath)) {
                @mkdir($dirpath);
                @chmod($dirpath, 0777);
            }
        }
    }

    //生成唯一标识
    public static function mkguid() {
        $charid = strtoupper(md5(uniqid(mt_rand(), true)));
        $hyphen = chr(45); // "-"
        $uuid = substr($charid, 0, 8) . $hyphen
                . substr($charid, 8, 4) . $hyphen
                . substr($charid, 12, 4) . $hyphen
                . substr($charid, 16, 4) . $hyphen
                . substr($charid, 20, 12);
        return $uuid;
    }

    // 写入错误
    public static function error($filename, $server, $msg, $addtime) {
        require_once LIB_DIR . 'class/DB.class.php';
        $sql = 'insert into errs(path,server,msg,time) values( :path, :server, :msg, :time)';
        $data = array($filename, $server, $msg, $addtime);
        DB::prepare($sql, $data);
    }

}
