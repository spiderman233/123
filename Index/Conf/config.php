<?php
include './Public/config.php';

return array(
    //'配置项'=>'配置值'
    'DEFAULT_V_LAYER'=>'Template',
    //'TMPL_TEMPLATE_SUFFIX'=>'.xxx', //修改模板后缀
    //'TMPL_FILE_DEPR'=>'_',
    'VIEW_PATH'=>'./Template/',

    'DB_TYPE'               =>  DB_TYPE,     // 数据库类型
    'DB_HOST'               =>  DB_HOST, // 服务器地址
    'DB_NAME'               =>  DB_NAME,          // 数据库名
    'DB_USER'               =>  DB_USER,      // 用户名
    'DB_PWD'                =>  DB_PWD,          // 密码
    'DB_PORT'               =>  DB_PORT,        // 端口
    'DB_CHARSET'            =>  DB_CHARSET,      // 数据库编码默认采用utf8
    'DB_PREFIX'             =>  DB_PREFIX,    // 数据库表前缀

    'URL_CASE_INSENSITIVE' => true,
    'URL_MODEL' => 2,

);