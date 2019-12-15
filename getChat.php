<?php
/**
 * CreateTime:2019-12-07 15:50
 * 获取聊天记录接口
 */


require_once './Base.php';

header('Access-Control-Allow-Origin:*');
header('Access-Control-Allow-Method:POST,GET');

$data=getChat($_GET['fromFd'],$_GET['toFd']);

successReturn($data);
