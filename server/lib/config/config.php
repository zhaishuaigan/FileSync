<?php

return array(
    // 图片存放路径
    'static' => LIB_DIR . '../static/',
    'static_thumb' => LIB_DIR . '../static_thumb/',
    // 工作模式  Server | Slave
    'mode' => 'Server',
    // 主服务器列表, 用于实时同步
    'server_list' => array(
        'http://server1.localhost/server/',
        'http://server2.localhost/server/',
    ),
);

