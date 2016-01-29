<?php
// +----------------------------------------------------------------------
// | yafwant
// +----------------------------------------------------------------------
// | Copyright (c) 2006-2014
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: huangbin
// +----------------------------------------------------------------------

/**
 * Class Redis_session
 * redis缓存驱动
 */
class Redis_session extends Cache{
    /*
     * 构造函数
     * @param array $options 缓存设置参数
     */
    public function __construct($options = array()){
        $this->options = $options;
        $this->session_handle = new Redis();
        /* pconnect为长连接,使用pconnect时，连接会被重用，连接的生命周期是fpm进程的生命周期，而非一次php的执行.
           如果代码中使用pconnect,close的作用仅是使当前php不能再进行redis请求，
           但无法真正关闭redis长连接,连接在后续请求中仍然会被重用,直至fpm进程生命周期结束 */
        $func = $options['persistent'] ? 'pconnect' : 'connect';
        $options['timeout'] === false || $options['timeout'] === null ?
        $this->session_handle->$func($options['host'], $options['port']):
        $this->session_handle->$func($options['host'], $options['port'], $options['timeout']);
    }

    /**
     * 读取缓存
     * @param $name 缓存变量名
     * return mix
     */
    public function get($name){
        //缓存值
        $value = $this->session_handle->get($name);
        //json转换,用以判断是否是json数据
        $jsonData  = json_decode($value, true);
        //是json数据返回转换的数组
        return ($jsonData === NULL) ? $value : $jsonData;
    }

    /**
     * 写入缓存
     * @access public
     * @param string $name 缓存变量名
     * @param mixed $value  存储数据
     * @param integer $expire  有效时间（秒）
     * @return boolean
     */
    public function set($name, $value, $expire = null) {
        if(is_null($expire)) {
            $expire  =  $this->options['expire'];
        }
        //对数组/对象数据进行缓存处理，保证数据完整性
        $value  =  (is_object($value) || is_array($value)) ? json_encode($value) : $value;
        if(is_int($expire) && !empty($expire)) {
            $result = $this->session_handle->setex($name, $expire, $value);
        }else{
            $result = $this->session_handle->set($name, $value);
        }
//        if($result && $this->options['length']>0) {
//            // 记录缓存队列
//            $this->queue($name);
//        }
        return $result;
    }

    /**
     * 删除缓存
     * @access public
     * @param string $name 缓存变量名
     * @return boolean
     */
    public function rm($name) {
        return $this->session_handle->delete($name);
    }
}