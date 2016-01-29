<?php
// +----------------------------------------------------------------------
// | Yafwant
// +----------------------------------------------------------------------
// | Copyright (c) 2015-2016
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: huangbin
// +----------------------------------------------------------------------

/**
 * Class Log
 * 日志类
 */
class Log {

    // 日志级别 从上到下，由低到高
    const EMERG     = 'EMERG';  // 严重错误: 导致系统崩溃无法使用
    const ALERT     = 'ALERT';  // 警戒性错误: 必须被立即修改的错误
    const CRIT      = 'CRIT';  // 临界值错误: 超过临界值的错误，例如一天24小时，而输入的是25小时这样
    const ERR       = 'ERR';  // 一般错误: 一般性错误
    const WARN      = 'WARN';  // 警告性错误: 需要发出警告的错误
    const NOTICE    = 'NOTIC';  // 通知: 程序可以运行但是还不够完美的错误
    const INFO      = 'INFO';  // 信息: 程序输出信息
    const DEBUG     = 'DEBUG';  // 调试: 调试信息
    const SQL       = 'SQL';  // SQL：SQL语句 注意只在调试模式开启时有效

    // 日志存储
    static protected $storage   =   null;

    //日志文件目录
    private static $log_path = LOG_PATH;

    /**
     * 日志直接写入
     * @static
     * @access public
     * @param string $message 日志信息
     * @param string $level  日志级别
     * @param integer $type 日志记录方式
     * @param string $destination  写入目标
     * @return void
     */
    static function write($message, $level=self::ERR, $type='', $destination='') {
        //获取上传配置
        $options = Yaf_Registry::get('logbase');
        //日志目录
        $log_path = !empty(self::$log_path) ? self::$log_path : $options['path'];
        if(!self::$storage){
            $type = $type ? $type : $options['type'];
            $class = ucwords($type);
            Yaf_loader::import("Log/Driver/".$class.".php");
            self::$storage = new $class();
        }
        if(empty($destination))
            $destination = $log_path.date('y_m_d').'.log';
        self::$storage->write("{$level}: {$message}", $destination);
    }
}