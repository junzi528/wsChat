<?php
/**
 * 基础功能文件，主要是对redis的操作
 * CreateTime:2019-12-07 16:17
 */


function jsonMsg($code, $message, $data)
{
    return json_encode(compact('code', 'message', 'data'));
}

function successReturn($data = [], $msg = 'success')
{
    exit(jsonMsg(200, $msg, $data));
}

function failReturn($data = [], $msg = 'fail')
{
    exit(jsonMsg(400, $msg, $data));
}

//连接redis
function getRedis(){
    static $redis=null;
    if($redis !== null){
        return $redis;
    }
    $redis=new redis();
    $redis->connect('127.0.0.1',6379);
    return $redis;
}

//将新用户，存到redis中
function newUser($data){
    getRedis()->hSet("users",$data['fd'],$data['nickname']);
}

//用户断开后，将它从redis中删除
function userOff($fd){
    getRedis()->hDel("users",$fd);
}

//获取所有在线用户
function getAllUser(){
    $data=getRedis()->hGetAll("users");

    $arr=[];
    foreach ($data as $k=>$v) {
        $arr[]=[
            'fd'=>$k,
            'nickname'=>$v,
            'hasNew'=>false
        ];
    }
    return $arr;
}

//一对一发送消息
function send($server,$fromFd,$data){
    $time=date('Y-m-d H:i:s');
    $fromNickname=getRedis()->hGet('users',$fromFd);
    //发送
    $server->push($data['toFd'],message('newMsg',['time'=>$time,'fromFd'=>$fromFd,'fromNickname'=>$fromNickname,'content'=>$data['content']]));

    //消息保存到redis
    $key=getChatKey($data['toFd'],$fromFd);
    $arr=[
        "toFd"=>$data['toFd'],
        "toNickname"=>getRedis()->hGet('users',$data['toFd']),
        "fromFd"=>$fromFd,
        "fromNickname"=>$fromNickname,
        "time"=>$time,
        "content"=>$data['content'],
    ];
    getRedis()->rPush($key,json_encode($arr));
}


//获取两个人之间的聊天记录
function getChat($fd1,$fd2){
    $key=getChatKey($fd1,$fd2);
    $data= getRedis()->lRange($key,0,-1);
    foreach ($data as &$v) {
        $v=json_decode($v,true);
    }
    return $data;
}

/**
 * 获取两个人之间聊天记录的key
 * 保持 fd1 < fd2 ，
 * 比如一个人的fd是8  另一个人是2 ，那么，他们聊天记录的Key就是 msg:2:8
 * @param $fd1
 * @param $fd2
 * @return string
 */
function getChatKey($fd1,$fd2){
    $key="msg:";
    if($fd1>$fd2){
        $key.=$fd2.':'.$fd1;
    }else{
        $key.=$fd1.':'.$fd2;
    }
    return $key;
}