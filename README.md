# ThinkPHP OCPC 客户端

一个简单的 ThinkPHP OCPC 客户端

## 安装
~~~
composer require axguowen/think-ocpcclient
~~~

## 使用

首先配置config目录下的ocpcclient.php配置文件。

~~~php
$ocpcClient = \think\facade\OcpcClient::platform('vivo', [
    'callback' => 'aa.dlgjlxs.com',
    'action_type' => 'CONFIRM_EFFECTIVE_LEADS',
    'click_id' => '8c97b615092c186e',
    'action_time' => '1685774968',
]);
// 获取回传结果并打印
$convert = $ocpcClient->convertGenerally();
var_dump($convert);
~~~
