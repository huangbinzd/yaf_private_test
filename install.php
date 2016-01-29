<?php

/**
 * 缓存目录设置
 * 此目录必须可写，建议移动到非WEB目录
 */
define ('RUNTIME_PATH', './runtime/');

define('APP_PATH', realpath(dirname(__FILE__)));
//创建runtime目录
if(!is_dir(RUNTIME_PATH)){
    mkdir(RUNTIME_PATH);
}
//是否存在application.ini文件
if(!is_file(APP_PATH . '/conf/application.ini') && is_file(APP_PATH . '/conf/application_example.ini')){
    $config_content = file_get_contents(APP_PATH.'/conf/application_example.ini');
    if(is_writable(APP_PATH.'/temp')) {
        file_put_contents(APP_PATH . '/conf/application.ini', $config_content);
    }
    //copy(APP_PATH.'/conf/application_example.ini',APP_PATH.'/conf/application.ini');
}
?>