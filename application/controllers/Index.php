<?php
class IndexController extends Yaf_Controller_Abstract {
   public function indexAction() {//默认Action
       try{
   		   $db = Db::getInstance();
           $result_arr = $db->getRow("SELECT user_id,username FROM shop_user WHERE user_id = 2");
           /*$i_arr = array(
             'username' => 'zhangsan',
           );*/
           //$id = $db->insert($i_arr,array('table' => 'shop_user'));
           //$result_arr_s = $db->query("SELECT * FROM shop_order");
       }
       catch(Exception $e){
           print_r($e->getMessage());
       }
       print_r($result_arr);
       //print_r($result_arr_s);
       $this->getView()->assign("content", "Hello");
   }
}
?>