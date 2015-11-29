<?php
class IndexController extends Yaf_Controller_Abstract {
   public function indexAction() {//默认Action
   		// $db = Db::getInstance();
//       $h = $db->connect();
//       $rs = $h->query("SELECT * FROM ecs_users");
//       $result_arr = $rs->fetchAll();
//       print_r($result_arr);
       $this->getView()->assign("content", "Hello");
   }
}
?>