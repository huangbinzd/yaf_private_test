<?php
   /**
   * @name Bootstrap
   * @author yantze
   * @desc 所有在Bootstrap类中, 以_init开头的方法, 都会被Yaf调用,
   * @see http://www.php.net/manual/en/class.yaf-bootstrap-abstract.php
   * 这些方法, 都接受一个参数:Yaf_Dispatcher $dispatcher
   * 调用的次序, 和申明的次序相同
   print_r(Yaf_Application::app());
   //require yaf_classes.php
   */
   class Bootstrap extends Yaf_Bootstrap_Abstract{

      private $_config;
      //初始化全局配置
      public function _initConfig() {
         //把配置保存起来
         $this->_config = Yaf_Application::app()->getConfig();
         Yaf_Registry::set('config', $this->_config);
      }
      //初始化数据库配置
      public function _initDatabase() {
         $servers = array();
         $database = $this->_config->database;
         $servers[] = $database->master->toArray();
         $slaves = $database->slaves;
         if (!empty($slaves))
         {
            $slave_servers = explode('|', $slaves->servers);
            $slave_users = explode('|', $slaves->users);
            $slave_passwords = explode('|', $slaves->passwords);
            $slave_databases = explode('|', $slaves->databases);
            $slaves = array();
            foreach ($slave_servers as $key => $slave_server)
            {
               if (isset($slave_users[$key]) && isset($slave_passwords[$key]) && isset($slave_databases[$key]))
               {
                  $slaves[] = array('server' => $slave_server, 'user' => $slave_users[$key], 'password' => $slave_passwords[$key], 'database' => $slave_databases[$key]);
               }
            }
            $servers[] = $slaves[array_rand($slaves)];
         }
         Yaf_Registry::set('database', $servers);
         if (isset($database->mysql_cache_enable) && $database->mysql_cache_enable && !defined('MYSQL_CACHE_ENABLE'))
         {
            define('MYSQL_CACHE_ENABLE', true);
         }
         if (isset($database->mysql_log_error) && $database->mysql_log_error && !defined('MYSQL_LOG_ERROR'))
         {
            define('MYSQL_LOG_ERROR', true);
         }
      }

   }
