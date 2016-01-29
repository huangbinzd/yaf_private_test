<?php
class IndexController extends Yaf_Controller_Abstract {
  public function indexAction() {//默认Action
    try{
		     /* mysql测试
		       $db = Db::getInstance();
           $result_arr = $db->getRow("SELECT user_id,username FROM shop_user WHERE user_id = 2");
           $i_arr = array(
             'username' => 'zhangsan',
           );
           $id = $db->insert($i_arr,array('table' => 'shop_user'));
           $result_arr_s = $db->query("SELECT * FROM shop_order");
		     */
        /* redis测试
       //$db = Db::getInstance();
       //$result_arr = $db->getRow("SELECT user_id,username FROM shop_user WHERE user_id = 2");
        $cache = Cache::getInstance();
        $w_ss = $cache->set("call_log",'fsdfsdfd',60);
        $ss = $cache->get("call_log");
        $flag = $cache->rm("call_log");
        //$tt = $cache->rm("call_log");
        */

        //Log::write('test log');
        $db = Db::getInstance();
        $result_arr = $db->getRow("SELECT user_id,username FROM shop_user WHERE user_id = 2");
        if(!$result_arr){
            $message = $db->error;
            Log::write($message,'SQL');
        }
        $path = $this->getViewPath();
    }
    catch(Exception $e){
      print_r($e->getMessage());
    }
       //print_r($result_arr);
       //print_r($result_arr_s);
    $this->getView()->assign("content", "Hello");
      Yaf_Dispatcher::getInstance()->disableView();
      $this->getView()->display("index/test.phtml");
  }

  public function testAction() {
    $this->getView()->assign("content", "test");
  }

  public function uploadAction() {
    echo "upload";
  }
}
?>