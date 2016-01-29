<?php
//程序根目录
define("APP_PATH", realpath(dirname(__FILE__)));
//程序运行时目录
define("RUNTIME_PATH", APP_PATH."/runtime/");
//缓存文件目录
define("TEMP_PATH",RUNTIME_PATH."/temp/");
//文件上传目录
define("UPLOAD_PATH",APP_PATH."/upload/");
//日志文件目录
define("LOG_PATH",RUNTIME_PATH."/log/");

if(!is_file(APP_PATH . '/conf/application.ini')){
    header('Location: ./install.php');
    exit;
}

$app  = new Yaf_Application(APP_PATH . "/conf/application.ini");
$app->bootstrap()->run();