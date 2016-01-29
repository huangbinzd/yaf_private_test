<?php
 	#!/usr/bin/php -q
    //echo  date('Y-m-d H:i:s')."from http://www.phpddt.com \n";
//     $redis = new Redis();
//     $redis->connect('127.0.0.1', 6379);
//     $r_len = $redis->lLen("call_log");
// //    echo $r_len;
// 	  try {
// 	  	$config = array(
// 	  		'server' => '127.0.0.1:3306',
// 	  		'user'	 => 'root',
// 	  		'password' => 'root',
// 	  		'database' => 'shop',
//             'type'   => 'mysql'
// 	  	);
// 	  	$link_2004 = mysql_connect($config['server'], $config['user'], $config['password']);
//     	$crowd_db = mysql_select_db($config['database'], $link_2004);
//           for($i = 0; $i < $r_len; $i++){
//             $inser_obj = json_decode($redis->lPop("call_log"));
//             $uid = $inser_obj->user_uuid;
//             $comment = $inser_obj->comment;
//               $insert_sql = "insert into shop_fav (`user_uuid`, `comment`) values ('$uid','$comment')";
//               $res = mysql_query($insert_sql);
//               $error_msg = mysql_error($link_2004);
//           }
// //          $insert_sql = "insert into shop_fav (`user_uuid`, `comment`) values ('123456df','rwesdf')";
// //          $res = mysql_query($insert_sql);
//         //$res = mysql_query("SELECT user_id,username FROM shop_user WHERE user_id = 2");
//         //$result_arr = mysql_fetch_assoc($res);
//           mysql_close($link_2004);
// 	  } catch (Exception $e) {
// 	  	echo $e->getMessage();
// 	  }
?>