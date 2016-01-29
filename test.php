<?php
  	$config = array(
  		'server' => '127.0.0.1:3306',
  		'user'	 => 'root',
  		'password' => 'root',
  		'database' => 'shop',
        'type'   => 'mysql'
  	);
  	$link_2004 = mysql_connect($config['server'], $config['user'], $config['password']);
	  $crowd_db = mysql_select_db($config['database'], $link_2004);
    for($i = 1; $i <= 10000; $i++){
        $insert_sql = "insert into shop_fav (`user_uuid`, `comment`) values ('123456df','rwesdf')";
        $res = mysql_query($insert_sql);
        $error_msg = mysql_error($link_2004);
    }
    //$res = mysql_query("INSERT user_id,username FROM shop_user WHERE user_id = 2");
?>