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
        $savefile = $GLOBALS['config']['static'] . $filename;
        $dir = dirname($savefile);
        self::cmkdir($dir);
        if (!move_uploaded_file($_FILES[$file]['tmp_name'], $savefile)) {
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
        $post_data = array(
            'file' => '@' . $GLOBALS['config']['static'] . $filename
        );
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_HEADER, false);
        //启用时会发送一个常规的POST请求，类型为：application/x-www-form-urlencoded，就像表单提交的一样。
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_BINARYTRANSFER, true);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $post_data);
        curl_setopt($ch, CURLOPT_URL, $server . '/' . $filename);
        $info = curl_exec($ch);
        curl_close($ch);
        if (!$info) {
            $info = json_encode(array(
                'success' => 0,
                'filename' => '',
                'msg' => '连接出错!'
            ));
        }
        return json_decode($info);
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

    // 返回404
    public static function return404() {
        header('HTTP/1.1 404 Not Found');
        header("status: 404 Not Found");
        die();
    }

    // 写入错误
    public static function error($filename, $server, $msg, $addtime) {
        require_once LIB_DIR . 'class/DB.class.php';
        DB::vacuum();
        $sql = 'insert into errs(path,server,msg,time) values( :path, :server, :msg, :time)';
        $data = array($filename, $server, $msg, $addtime);
        DB::prepare($sql, $data);
    }

}
