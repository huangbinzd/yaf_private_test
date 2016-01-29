<?php
class ProductController extends Yaf_Controller_Abstract {

	public function indexAction() {
		echo "index";
		$routes = Yaf_Dispatcher::getInstance()->getRouter()->getRoutes();
		print_r($routes); 
	}

    public function uploadAction() {
        //echo "das";
        $upload = new Upload($config = array());
        $upload->upload();
    }
}