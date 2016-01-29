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

//namespace library;

/**
 * 缓存管理
 * @todo 添加错误处理
 */
 class Cache {
 	/**
     * 缓存连接参数
     * @access protected
     */
    protected $options = array();
    /**
     * 连接缓存
     * @access public
     * @param string $type 缓存类型
     * @param array $options  配置数组
     * @return object
     */
    public function connect($type='',$options=array()) {
        if (empty($options)) {
            $options = Yaf_Registry::get('cachebase');
        }
        if(empty($type)){
            $type = $options['type'];
        }
        $class  =   strpos($type,'\\')? $type : ucwords(strtolower($type));
        //要载入的文件路径, 可以为绝对路径和相对路径. 如果为相对路径, 则会以应用的本地类目录(ap.library)为基目录
        Yaf_loader::import("Cache/Driver/".$class.".php");
        $class_name = $class."_session";
//        if(class_exists($class_name))
            $cache = new $class_name($options);
//        else
//            trigger_error('not found this cache class',E_USER_ERROR);
        return $cache;
    }
    /**
     * 取得缓存类实例
     * @static
     * @access public
     * @return mixed
     */
    static function getInstance($type='',$options=array()) {
		static $_instance =	array();
        if (empty($type)) {
            $type = $options['type'];
        }
		$guid =	md5(serialize($options));
		if(!isset($_instance[$guid])){
			$obj = new Cache();
			$_instance[$guid] =	$obj->connect($type,$options);
		}
		return $_instance[$guid];
    }

     public function __get($name) {
         return $this->get($name);
     }

    /**
     * 调用缓存类型自身的方法
     * php重载方法,为了避免当调用的方法不存在时产生错误，可以使用 __call() 方法来避免。该方法在调用的方法不存在时会自动调用，程序仍会继续执行下去
     * @param $method 方法名
     * @param $args 调用方法参数
     */
    public function __call($method,$args){
     //调用缓存类型自己的方法
        if(method_exists($this->session_handle, $method)){
            return call_user_func_array(array($this->session_handle,$method), $args);
        }else{
         //E(__CLASS__.':'.$method.L('_METHOD_NOT_EXIST_'));
            return;
        }
    }
 }