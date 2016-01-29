<?php
	$redis = new Redis();
	$redis->connect('127.0.0.1', 6379);
	// 加上时间戳存入队列
	$now_time = date("Y-m-d H:i:s");
	$interface_info = array(
		'user_uuid' => '123456df',
		'comment'   => 'rwesdf'
	);
    $r_len = $redis->lLen("call_log");
//    for($i = 0; $i < $r_len; $i++){
//        $redis->lPop("call_log");
//    }
	$interface_info = json_encode($interface_info);
    for($i = 1; $i <= 10000; $i++) {
        $redis->rPush("call_log", $interface_info);
    }
?>