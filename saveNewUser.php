<?php
/**
 * CreateTime:2019-12-07 22:53
 * 新增用户接口
 */

require_once './Base.php';

header('Access-Control-Allow-Origin:*');
header('Access-Control-Allow-Method:POST,GET');

$users = getAllUser();//先获取列表，以便排除掉马上新增的自己
if (empty($_GET['fd'] || empty($_GET['nickname']))) {
    failReturn($users);
}
newUser($_GET);
successReturn($users);