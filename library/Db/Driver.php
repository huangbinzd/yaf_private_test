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

/**
 *  数据库驱动类
 *  PDO 操作数据库方法fetch()
 *  语法 PDOStatement->fetch(int mode)
 *  mode 参数可取值如下：
 *  PDO::FETCH_ASSOC	关联索引（字段名）数组形式
 *  PDO::FETCH_NUM	数字索引数组形式
 *  PDO::FETCH_BOTH	默认，关联及数字索引数组形式都有
 *  PDO::FETCH_OBJ	按照对象的形式
 *  PDO::FETCH_BOUND	通过 bindColumn() 方法将列的值赋到变量上
 *  PDO::FETCH_CLASS	以类的形式返回结果集，如果指定的类属性不存在，会自动创建
 *  PDO::FETCH_INTO	将数据合并入一个存在的类中进行返回
 *  PDO::FETCH_LAZY	 结合了 PDO::FETCH_BOTH、PDO::FETCH_OBJ，在它们被调用时创建对象变量
 *  PDO 操作数据库方法fetchAll()
 *  语法 PDOStatement->fetchAll(int mode,int column_index)
 *  mode 为可选参数，表示希望返回的数组，column_index 表示列索引序号，当 mode 取值 PDO::FETCH_COLUMN 时指定
 *  mode 参数可取值如下：
 *  PDO::FETCH_COLUMN	指定返回返回结果集中的某一列，具体列索引由 column_index 参数指定
 *  PDO::FETCH_UNIQUE	以首个键值下表，后面数字下表的形式返回结果集
 *  PDO::FETCH_GROUP	按指定列的值分组
 *  PDO quote方法 为字符串添加单引号
 *  语法 PDOStatement->quote(string str)
 */
abstract class Driver {
    // PDO操作实例
    protected $PDOStatement = null;
	// 数据库连接ID 支持多个连接
    protected $linkID     = array();
    // 当前连接ID
    protected $_linkID    = null;
    // 最后插入ID
    protected $lastInsID  = null;
    // 当前SQL指令
    protected $queryStr   = '';
    // 错误信息
    public $error      = '';
    // 查询次数
    protected $queryTimes   =   0;
    // 执行次数
    protected $executeTimes =   0;
    // 返回或者影响记录数
    protected $numRows    = 0;
    // 数据库连接参数配置
    protected $config     = array();
    // PDO连接参数
    protected $pdo_options = array(
        PDO::ATTR_CASE              =>  PDO::CASE_LOWER,
        PDO::ATTR_ERRMODE           =>  PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_ORACLE_NULLS      =>  PDO::NULL_NATURAL,
        PDO::ATTR_STRINGIFY_FETCHES =>  false,
    );
    // 参数绑定
    protected $bind         =   array();
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
    public function connect($config = '',$link_Num=0,$autoConnection=false){
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
                if($autoConnection){
                    //trace($e->getMessage(),'','ERR');
                    return $this->connect($autoConnection,$link_Num);
                }elseif($config['debug']){
                    //E($e->getMessage());
                    $this->error();
                }
                else{
                    $this->error();
                }
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
        if(!empty($this->config['deploy_type'])){
            // 采用分布式数据库
            $this->_linkID = $this->multiConnect($master);
        }
        else{
            // 默认单数据库
            if ( !$this->_linkID ) $this->_linkID = $this->connect();
        }
    }
    /**
     * 连接分布式服务器
     * @access protected
     * @param boolean $master 主服务器
     * @return void
     */
    protected function multiConnect($master=false) {
        // 分布式数据库配置解析
        $_config['username']    =   explode('|',$this->config['user']);
        $_config['password']    =   explode('|',$this->config['password']);
        $_config['hostname']    =   explode('|',$this->config['server']);
        $_config['hostport']    =   explode('|',$this->config['port']);
        $_config['database']    =   explode('|',$this->config['database']);
        $_config['dsn']         =   explode('|',$this->config['dsn']);
        $_config['charset']     =   explode('|',$this->config['charset']);

        $m     =   floor(mt_rand(0,$this->config['master_num']-1));
        // 数据库读写是否分离
        if($this->config['rw_separate']){
            // 主从式采用读写分离
            if($master){
                // 主服务器写入
                $r  =   $m;
            }
            else{
                if(is_numeric($this->config['slave_no'])) {// 指定服务器读
                    $r = $this->config['slave_no'];
                }else{
                    // 读操作连接从服务器
                    $r = floor(mt_rand($this->config['master_num'],count($_config['hostname'])-1));   // 每次随机连接的数据库
                }
            }
        }else{
            // 读写操作不区分服务器
            $r = floor(mt_rand(0,count($_config['hostname'])-1));   // 每次随机连接的数据库
        }
        
        if($m != $r ){
            $db_master  =   array(
                'username'  =>  isset($_config['username'][$m])?$_config['username'][$m]:$_config['username'][0],
                'password'  =>  isset($_config['password'][$m])?$_config['password'][$m]:$_config['password'][0],
                'hostname'  =>  isset($_config['hostname'][$m])?$_config['hostname'][$m]:$_config['hostname'][0],
                'hostport'  =>  isset($_config['hostport'][$m])?$_config['hostport'][$m]:$_config['hostport'][0],
                'database'  =>  isset($_config['database'][$m])?$_config['database'][$m]:$_config['database'][0],
                'dsn'       =>  isset($_config['dsn'][$m])?$_config['dsn'][$m]:$_config['dsn'][0],
                'charset'   =>  isset($_config['charset'][$m])?$_config['charset'][$m]:$_config['charset'][0],
            );
        }
        $db_config = array(
            'user'  =>  isset($_config['username'][$r])?$_config['username'][$r]:$_config['username'][0],
            'password'  =>  isset($_config['password'][$r])?$_config['password'][$r]:$_config['password'][0],
            'server'  =>  isset($_config['hostname'][$r])?$_config['hostname'][$r]:$_config['hostname'][0],
            'port'  =>  isset($_config['hostport'][$r])?$_config['hostport'][$r]:$_config['hostport'][0],
            'database'  =>  isset($_config['database'][$r])?$_config['database'][$r]:$_config['database'][0],
            'dsn'       =>  isset($_config['dsn'][$r])?$_config['dsn'][$r]:$_config['dsn'][0],
            'charset'   =>  isset($_config['charset'][$r])?$_config['charset'][$r]:$_config['charset'][0],
        );
        return $this->connect($db_config,$r,$r == $m ? false : $db_master);
    }
    /**
     * 关闭数据库
     * @access public
     */
    public function close() {
        $this->_linkID = null;
    }
    /**
     * 执行语句(插入或更新)
     * @access public
     * @param string $str sql指令
     * @return mixed
     */
    public function exec($str){
        return $this->execute($str);
    }
    /**
     * 执行查询 返回单列数据
     * @access public
     * @param string $str  sql指令
     * @return mixed
     */
    public function getOne($str) {
        if($this->query($str)){
            $query_result = $this->getOneResult();
        }
        return $query_result;
    }
    /**
     * 执行查询 返回单行数据
     * @access public
     * @param string $str  sql指令
     * @return mixed
     */
    public function getRow($str) {
        if($this->query($str)){
            $query_result = $this->getRowResult();
        }
        return $query_result;
    }
    /**
     * 执行查询 返回数据集
     * @access public
     * @param string $str  sql指令
     * @return mixed
     */
    public function getAll($str) {
        if($this->query($str)){
             $query_result = $this->getAllResult();
        }
        return $query_result;
    }
    /**
     * 执行插入 返回主键id
     * @access public
     * @param mixed $data
     * @param array $options 参数表达式
     * @return false | integer
     */
    public function insert($data,$options=array()) {
        //操作的数据库表
        $table = $options['table'];
        //数据值数组
        $values = array();
        //字段名数组
        $fields = array();
        foreach ($data as $key => $val) {
            if(is_null($val)){
                $values[] = 'NULL';
            }
            //字符串添加单引号
            $values[] = $this->_linkID->quote($val);
            $fields[] = $key;
        }
        if($this->execute('INSERT INTO '.$table.' ('.implode(',',$fields).') VALUES ('.implode($values,',').')')){
            return $this->lastInsID;
        };
        return false;
    }
    /**
     * 执行更新
     * @access public
     * @param mixed $data
     * @param array $options 参数表达式
     * @return bool
     */
    public function update($data,$options=array()) {
        //操作的数据库表
        $table = $options['table'];
        //数据值数组
        $values = array();
        //字段名数组
        $fields = array();
        foreach ($data as $key => $val) {
            if(is_null($val)){
                $values[] = 'NULL';
            }
            //字符串添加单引号
            $values[] = $this->_linkID->quote($val);
            $fields[] = $key;
        }
        if($this->execute('UPDATE '.$table.' ('.implode(',',$fields).') VALUES ('.implode($values,',').')')){
            return $this->lastInsID;
        };
        return false;
    }
    /**
     * 执行查询 返回数据集
     * @access private
     * @param string $str  sql指令
     * @param boolean $fetchSql  不执行只是获取SQL
     * @return mixed
     */
    private function query($str,$fetchSql=false) {
        $this->startConnect(false);
        if ( !$this->_linkID ) return false;
        $this->queryStr = $str;
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
        $this->PDOStatement = $this->_linkID->prepare($str);
        if(false === $this->PDOStatement)
            $this->error();
        foreach ($this->bind as $key => $val) {
            if(is_array($val)){
                $this->PDOStatement->bindValue($key, $val[0], $val[1]);
            }else{
                $this->PDOStatement->bindValue($key, $val);
            }
        }
        $this->bind = array();
        try{
            $result = $this->PDOStatement->execute();
            if ( false === $result ) {
                $this->error();
                return false;
            } else {
                return true;
            }
        }catch (\PDOException $e) {
            $this->error();
            return false;
        }
    }
    /**
     * 执行语句
     * @access private
     * @param string $str  sql指令
     * @param boolean $fetchSql  不执行只是获取SQL
     * @return mixed
     */
    private function execute($str,$fetchSql=false) {
        $this->startConnect(true);
        if ( !$this->_linkID ) return false;
        $this->queryStr = $str;
        if(!empty($this->bind)){
            $that   =   $this;
            $this->queryStr = strtr($this->queryStr,array_map(function($val) use($that){ return '\''.$that->escapeString($val).'\''; },$this->bind));
        }
        if($fetchSql){
            return $this->queryStr;
        }
        //释放前次的查询结果
        if ( !empty($this->PDOStatement) ) $this->free();
        $this->executeTimes++;
        $this->PDOStatement =   $this->_linkID->prepare($str);
        if(false === $this->PDOStatement) {
            $this->error();
            return false;
        }
        foreach ($this->bind as $key => $val) {
            if(is_array($val)){
                $this->PDOStatement->bindValue($key, $val[0], $val[1]);
            }else{
                $this->PDOStatement->bindValue($key, $val);
            }
        }
        $this->bind = array();
        try{
            $result = $this->PDOStatement->execute();
            if ( false === $result) {
                $this->error();
                return false;
            } else {
                $this->numRows = $this->PDOStatement->rowCount();
                if(preg_match("/^\s*(INSERT\s+INTO|REPLACE\s+INTO)\s+/i", $str)) {
                    //获取最近插入语句返回的主键id
                    $this->lastInsID = $this->_linkID->lastInsertId();
                    //插入返回主键id
                    return $this->lastInsID;
                }
                return $this->numRows;
            }
        }catch (\PDOException $e) {
            $this->error();
            return false;
        }
    }
    /**
     * 释放查询结果
     * @access public
     */
    public function free() {
        $this->PDOStatement = null;
    }
    /**
     * SQL指令安全过滤
     * @access public
     * @param string $str  SQL字符串
     * @return string
     */
    public function escapeString($str) {
        return addslashes($str);
    }
    /**
     * 获得所有的查询数据
     * @access private
     * @return mix
     */
    private function getOneResult() {
        //返回单列数据
        $result = $this->PDOStatement->fetch(PDO::FETCH_COLUMN);
        return $result;
    }
    private function getRowResult() {
        //返回单行数据
        $result = $this->PDOStatement->fetch(PDO::FETCH_ASSOC);
        return $result;
    }
    /**
     * 获得所有的查询数据
     * @access private
     * @return array
     */
    private function getAllResult() {
        //返回数据集
        $result =   $this->PDOStatement->fetchAll(PDO::FETCH_ASSOC);
        $this->numRows = count( $result );
        return $result;
    }
    /**
     * 数据库错误信息
     * 并显示当前的SQL语句
     * @access public
     * @return string
     */
    public function error() {
        if($this->PDOStatement) {
            $error = $this->PDOStatement->errorInfo();
            $this->error = $error[1].':'.$error[2];
        }else{
            $this->error = '';
        }
        if('' != $this->queryStr){
            $this->error .= "\n [ SQL语句 ] : ".$this->queryStr;
        }
        // 记录错误日志
        //exit($this->error);
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