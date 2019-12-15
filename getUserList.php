<?php
/**
 * CreateTime:2019-12-07 15:50
 * 获取用户列表接口
 */


require_once './Base.php';

header('Access-Control-Allow-Origin:*');
header('Access-Control-Allow-Method:POST,GET');

successReturn(getAllUser());

