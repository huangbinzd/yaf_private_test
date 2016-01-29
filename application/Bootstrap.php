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
  //配置变量
  private $_config;
  //初始化全局配置
  public function _initConfig() {
    //把配置保存起来
    $this->_config = Yaf_Application::app()->getConfig();
    Yaf_Registry::set('config', $this->_config);
  }
  //初始化路由配置
  public function _initRoute(Yaf_Dispatcher $dispatcher) {
    // $routes = $this->_config->routes;
    // //不为空
    // if (!empty($routes)) {
    //   //通过派遣器得到默认的路由器
    //   $router = Yaf_Dispatcher::getInstance()->getRouter();
    //   //添加配置中的路由
    //   $router->addConfig($routes);
        $route = new Yaf_Route_Rewrite( 'product/:ident',array('controller' => 'Product', 'action' => 'upload'));
      Yaf_Dispatcher::getInstance()->getRouter()->addRoute(
        "product", $route
      );
    // }
  }
  //初始化数据库配置
  public function _initDatabase() {
    $servers = array();
    $database = $this->_config->database;
    $servers[] = $database->toArray();
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
  //初始化缓存配置
  public function _initCacheConfig() {
    $cache_config = $this->_config->datacache;
    $cache_config_array = $cache_config->toArray();
    Yaf_Registry::set('cachebase', $cache_config_array);
  }
   //初始化文件上传配置
  public function _initUploadConfig() {
    $upload_config = $this->_config->fileupload;
    $upload_config_array = $upload_config->toArray();
    Yaf_Registry::set('uploadbase', $upload_config_array);
  }
  //初始化日志配置
  public function _initLogConfig() {
    $log_config = $this->_config->log;
    $log_config_array = $log_config->toArray();
    Yaf_Registry::set('logbase', $log_config_array);
  }
}
