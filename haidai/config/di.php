<?php
/**
 * DI依赖注入配置文件
 * 
 * @license     http://www.phalapi.net/license GPL 协议
 * @link        http://www.phalapi.net/
 * @author      dogstar <chanzonghuang@gmail.com> 2017-07-13
 */

use PhalApi\Loader;
use PhalApi\Config\FileConfig;
use PhalApi\Logger;
use PhalApi\Logger\FileLogger;
use PhalApi\Database\NotORMDatabase;

/** ---------------- 基本注册 必要服务组件 ---------------- **/

$di = \PhalApi\DI();

// 配置
$di->config = new FileConfig(API_ROOT . '/config');

// 调试模式，$_GET['__debug__']可自行改名
$di->debug = !empty($_GET['__debug__']) ? true : $di->config->get('sys.debug');
// $di->debug = true;

// 日记纪录
$di->logger = new FileLogger(API_ROOT . '/runtime', Logger::LOG_LEVEL_DEBUG | Logger::LOG_LEVEL_INFO | Logger::LOG_LEVEL_ERROR);

// 数据操作 - 基于NotORM
$di->notorm = new NotORMDatabase($di->config->get('dbs'), $di->debug);



// 数据操作 - 基于NotORM - 重定义创建PDO实例的方法
// $di->notorm = new \Database\NotORMDatabase($di->config->get('dbs'), $di->debug);


//tool工具
$di->tool = function () {
    return new \PhalApi\Tool();
};

// JSON中文输出
// $di->response = new \PhalApi\Response\JsonResponse(JSON_UNESCAPED_UNICODE);

/** ---------------- 定制注册 可选服务组件 ---------------- **/

// 签名验证服务
// $di->filter = new \PhalApi\Filter\SimpleMD5Filter();
$di->filter = new \App\Common\SimpleMD5Filter();

// 缓存 - Memcache/Memcached
$di->cache = function () {
    return new \PhalApi\Cache\MemcacheCache(\PhalApi\DI()->config->get('sys.mc'));
};

// 支持JsonP的返回
if (!empty($_GET['callback'])) {
    $di->response = new \PhalApi\Response\JsonpResponse($_GET['callback']);
}



$mq = new \PhalApi\Task\MQ\FileMQ();  //可以选择你需要的MQ
$di->taskLite = new \PhalApi\Task\Lite($mq);

// 生成二维码扩展，参考示例：?s=App.Examples_QrCode.Png
// $di->qrcode = function() {
//     return new \PhalApi\QrCode\Lite();
// };

// 注册扩展的追踪器，将SQL写入日志文件
// $di->tracer = function() {
//     return new \App\Common\Tracer();
// };
