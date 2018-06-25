<?php

$redis = new Redis();
$redis->connect('redis', 6379);
$redis->set('test', 'test_value');
var_dump($redis->get('test'));
$redis->close();
