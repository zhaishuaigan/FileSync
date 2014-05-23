<?php

define('LIB_DIR', './lib/');
require 'lib/class/App.class.php';
$info = App::uploadFile('file');
$result = array(
    'success' => 0,
    'filename' => '',
    'msg' => '无上传文件!'
);
if ($info['success']) {
    $result = array(
        'success' => 1,
        'filename' => $info['filename'],
        'msg' => '上传成功!'
    );
    if ($GLOBALS['config']['mode'] == 'Server') {
        foreach ($GLOBALS['config']['server_list'] as $key => $val) {
            // $syncinfo是同步返回的结果, 可以根据里面的信息判断是否同步成功
            $syncinfo = App::fileSync($val, '/' . $info['filename']);
            if (!$syncinfo || !$syncinfo->success) {
                App::error($info['filename'], $val, '同步失败!', time());
            }
        }
    }
} else {
    $result = array(
        'success' => 0,
        'filename' => '',
        'msg' => $info['msg']
    );
}
echo str_replace('\/', '/', json_encode($result));
