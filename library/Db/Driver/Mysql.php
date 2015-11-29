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

use Db\Driver;

/**
 * mysql数据库驱动 
 */
class Mysql extends Driver{
    /**
     * 解析pdo连接的dsn信息
     * @access public
     * @param array $config 连接信息
     * @return string
     */
    protected function parseDsn($config){
        $dsn  =   'mysql:dbname='.$config['database'].';host='.$config['server'];
        if(!empty($config['port'])) {
            $dsn  .= ';port='.$config['port'];
        }elseif(!empty($config['socket'])){
            $dsn  .= ';unix_socket='.$config['socket'];
        }

        if(!empty($config['charset'])){
            //为兼容各版本PHP,用两种方式设置编码
            $this->options[\PDO::MYSQL_ATTR_INIT_COMMAND]    =   'SET NAMES '.$config['charset'];
            $dsn  .= ';charset='.$config['charset'];
        }
        return $dsn;
    }
}
