# 用swoole实现的最简单的一对一网页聊天

## 简介
- 用于学习swoole，只有最简单的基本功能。打开网页即可聊天，不用注册。关闭网页后，用户就消失。
- 后端使用原生php和redis扩展，前端使用了vue
- redis主要用于管理在线用户和保存聊天记录

## 使用方法

1. 在安装了swoole和redis扩展的php环境下，在命令行中执行 php swoole.php，开启服务
2. 在浏览器中打开user.html文件，可以多开几个窗口，模拟多人聊天。
3. 输入昵称后，选择任何一个人，即可开始一对一聊天。（至少同时有两个人在线才能进行）
4. 若有其它人给你发来新消息，它的名字后面会有红点提示。

## 文件说明
1. swoole.php 用swoole创建的websocket服务
2. Base.php 基础文件，主要是对redis的操作
3. saveNewUser.php 有新用户连接后，需要调用的接口
4. getUserList.php 获取用户列表接口
5. getChat.php 获取聊天记录接口
6. user.html 前端页面，使用了vue，前端不好，写得比较烂
