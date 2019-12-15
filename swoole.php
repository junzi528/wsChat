<?php
/**
 * CreateTime:2019/12/07
 * websocket 服务
 * 需要在命令行中，使用 php swoole.php 来运行
 */

require_once './Base.php';

/**
 * 创建服务器
 */
//0.0.0.0 表示所有的ip都可以来连接,9503是端口号
$server= new swoole_websocket_server("0.0.0.0","9503");

/**
 * 在服务器上注册事件
 */

$server->on('start',function ($server){
    getRedis()->flushAll();//清空redis
    echo '已启动服务' . PHP_EOL;
});

/**
 * 当有客户端连接上时的回调
 * $server:websocket服务器
 * $req:客户端发来的消息
 * $req->fd 客户端唯一编号
 */
$server->on('open',function($server,$req){
    echo "有人连接上了，它的fd是:{$req->fd}\n";
    //将fd发送给连接者，然后他会将fd和昵称，存入redis
    $server->push($req->fd,message('newFd',['fd'=>$req->fd]));

    //在新用户上线时，redis还没存成功，不能立即获取到昵称，所以需要等一会
    for ($i = 0; $i < 5; $i++) {
        echo '尝试读取改昵称...' . PHP_EOL;
        sleep(1);
        $nickname=getRedis()->hGet('users',$req->fd);
        if(!empty($nickname)){
            break;
        }
    }

    echo '昵称:'.$nickname . PHP_EOL;

    //通知所有用户，有人上线
    foreach ($server->connections as $fd) {
        $server->push($fd,message('userIn',['fd'=>$req->fd,'nickname'=>$nickname]));
    }
});


/**
 * 当客户端向服务端发消息时的回调
 * 必须有的回调
 * $server:websocket服务器
 * $frame:客户端发来的消息
 * $frame->fd 客户端唯一编号
 * $frame->data 客户端发送消息的文本内容
 */
$server->on('message',function($server,$frame){
    echo '收到消息了:'.$frame->data . PHP_EOL;
    echo '发来消息的人是:'.$frame->fd . PHP_EOL;

    //解析出客户端发来的数据，根据不同的action，调用不同的处理方法
    $data=json_decode($frame->data,true);
    call_user_func_array($data['action'],[$server,$frame->fd,$data['data']]);
});


/**
 * 当断开连接时回调
 * 不管是由客户端发起断开,还是由服务端发起断开,都会执行
 */
$server->on('close',function($server,$off_fd){
    echo "断开连接:{$off_fd}\n";
    //从redis中删除
    userOff($off_fd);

    //通知所有用户，有人下线
    foreach ($server->connections as $fd) {
        if($fd!=$off_fd){
            $server->push($fd,message('userOff',['fd'=>$off_fd]));
        }
    }

});



/**
 * 启动服务器
 */
$server->start();

function message($action,$data=[]){
    return json_encode(compact('action','data'));
}