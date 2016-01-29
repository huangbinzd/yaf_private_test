<?php
// +----------------------------------------------------------------------
// | yaf
// +----------------------------------------------------------------------
// | Copyright (c) 2006-2014
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: huangbin
// +----------------------------------------------------------------------

/**
 * yaf 数据库中间层实现类
 */
class Db {
    static private  $instance   =  array();     //  数据库连接实例
    static private  $_instance  =  null;   //  当前数据库连接实例

    /**
     * 取得数据库类实例
     * @static
     * @access public
     * @param mixed $config 连接配置
     * @return Object 返回数据库驱动类
     */
    static public function getInstance($config=array()) {
    	if (!$config) {
    		$config = Yaf_Registry::get('database');
    	}
        $id_server = count($config) == 1 ? 0 : 1;
        $md5 = md5(serialize($config));
        if(!isset(self::$instance[$md5])) {
            // 兼容mysqli
            if('mysqli' == $config[$id_server]['type']) $config[$id_server]['type']   =   'mysql';
            // 如果采用lite方式 仅支持原生SQL 包括query和execute方法
            $class  = ucwords(strtolower($config[$id_server]['type']));
            Yaf_Loader::import(dirname(__FILE__) . '/Db/Driver/' . $class . '.php');
            // if(file_exists($class.'.php')){
                self::$instance[$md5] = new $class($config[$id_server]);
            //}
        }
        self::$_instance = self::$instance[$md5];
        return self::$_instance;
    }
    public function __clone(){
        trigger_error('Clone is not allow!',E_USER_ERROR);
    }
}