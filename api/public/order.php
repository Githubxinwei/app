<?php
// +----------------------------------------------------------------------
// | ThinkPHP [ WE CAN DO IT JUST THINK ]
// +----------------------------------------------------------------------
// | Copyright (c) 2006-2016 http://thinkphp.cn All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: liu21st <liu21st@gmail.com>
// +----------------------------------------------------------------------

// [ 应用入口文件 ]
// 通过任务计划自动执行代码 把订单未支付的库存返回去
// 定义应用目录
define('APP_PATH', __DIR__ . '/../application/');
define('WEB_PATH', __DIR__);
header("Access-Control-Allow-Origin:*");
define('DATA_ROOT','./data/');
define('STATIC_APTH',__DIR__ . '/../public/static/');
header("Content-type:text/html;charset=utf-8");
// 加载框架引导文件
require __DIR__ . '/../xigua/start.php';