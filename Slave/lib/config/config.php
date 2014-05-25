<?php

$GLOBALS['config'] = array(
    // 原图存放目录
    'static' => './static/',
    // 缩放后的图片路径
    'static_thumb' => './static_thumb/',
    // 主服务器列表
    'server_list' => array(
        'server_1' => 'http://localhost/server/get.php',
    ),
    // 允许的图片尺寸和别名
    'src_size' => 'src',
    'size' => array(
        's' => '320x240', // 小号
        'm' => '640x480', // 中号
        'l' => '1024x768', // 原始尺寸
    // ...可以自定义更多尺寸 
    ),
    // 是否开启自动识别图片尺寸和生成图片功能,
    //  注: 开启此项比较危险, 可能会被恶意生成很多无用图片
    'autosize' => false
);
