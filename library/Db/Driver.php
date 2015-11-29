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

namespace Db;
use PDO;

abstract class Driver {
	// 数据库连接ID 支持多个连接
    protected $linkID     = array();
    // 当前连接ID
    protected $_linkID    = null;
    // 数据库连接参数配置
    protected $config     = array();
    // PDO连接参数
    protected $pdo_options = array(
        PDO::ATTR_CASE              =>  PDO::CASE_LOWER,
        PDO::ATTR_ERRMODE           =>  PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_ORACLE_NULLS      =>  PDO::NULL_NATURAL,
        PDO::ATTR_STRINGIFY_FETCHES =>  false,
    );
    /**
     * 读取数据库配置信息
     * @param array $config 数据库配置数组
     */
    public function __construct($config=''){
        if(!empty($config)) {
            $this->config = $config;
        }
    }
    /**
     * 解析pdo连接的dsn信息
     * @access public
     * @param array $config 连接信息
     * @return string
     */
    protected function parseDsn($config){}
    /**
     * 连接数据库方法
     * author huangbin
     */
    public function connect($config = '',$link_Num=0){
        if ( !isset($this->linkID[$link_Num]) ) {
            if(empty($config)) $config = $this->config;
            try{
                if(empty($config['dsn'])) {
                    $config['dsn']  =   $this->parseDsn($config);
                }
                if(version_compare(PHP_VERSION,'5.3.6','<=')){ 
                    // 禁用模拟预处理语句
                    $this->pdo_options[PDO::ATTR_EMULATE_PREPARES] = false;
                }
                $this->linkID[$link_Num] = new PDO( $config['dsn'], $config['user'], $config['password'],$this->pdo_options);
            }catch (\PDOException $e) {
                // if($autoConnection){
                //     trace($e->getMessage(),'','ERR');
                //     return $this->connect($autoConnection,$linkNum);
                // }elseif($config['debug']){
                //     E($e->getMessage());
                // }
            }
        }
        return $this->linkID[$link_Num];    	
    }

    /**
     * 初始化数据库连接
     * @access protected
     * @param boolean $master 主服务器
     * @return void
     */
    protected function startConnect($master=true) {
        if(!empty($this->config['deploy']))
            // 采用分布式数据库
            $this->_linkID = $this->multiConnect($master);
        else
            // 默认单数据库
            if ( !$this->_linkID ) $this->_linkID = $this->connect();
    }

    /**
     * 关闭数据库
     * @access public
     */
    public function close() {
        $this->_linkID = null;
    }

    /**
     * 执行查询 返回数据集
     * @access public
     * @param string $str  sql指令
     * @param boolean $fetchSql  不执行只是获取SQL
     * @return mixed
     */
    public function query($str,$fetchSql=false) {
        $this->initConnect(false);
        if ( !$this->_linkID ) return false;
        $this->queryStr     =   $str;
        if(!empty($this->bind)){
            $that   =   $this;
            $this->queryStr =   strtr($this->queryStr,array_map(function($val) use($that){ return '\''.$that->escapeString($val).'\''; },$this->bind));
        }
        if($fetchSql){
            return $this->queryStr;
        }
        //释放前次的查询结果
        if ( !empty($this->PDOStatement) ) $this->free();
        $this->queryTimes++;
        N('db_query',1); // 兼容代码
        // 调试开始
        $this->debug(true);
        $this->PDOStatement = $this->_linkID->prepare($str);
        if(false === $this->PDOStatement)
            E($this->error());
        foreach ($this->bind as $key => $val) {
            if(is_array($val)){
                $this->PDOStatement->bindValue($key, $val[0], $val[1]);
            }else{
                $this->PDOStatement->bindValue($key, $val);
            }
        }
        $this->bind =   array();
        $result =   $this->PDOStatement->execute();
        // 调试结束
        $this->debug(false);
        if ( false === $result ) {
            $this->error();
            return false;
        } else {
            return $this->getResult();
        }
    }
   /**
     * 析构方法
     * @access public
     */
    public function __destruct() {
        // 关闭连接
        $this->close();
    }
}